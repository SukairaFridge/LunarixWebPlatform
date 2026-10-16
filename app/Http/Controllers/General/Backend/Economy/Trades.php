<?php
namespace App\Http\Controllers\General\Backend\Economy;
use App\Models\Inventory;
use App\Models\Economy\Trade;
use App\Models\Economy\TradeItem;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Trades
{
    private const TRADING_ENABLED = true; //TODO: LUNARIX_TRADING_ENABLED
    private const MAX_ITEMS_PER_SIDE = 4;
    private const TRADE_EXPIRY_SECONDS = 60 * 60 * 24 * 14;
    private const PAGE_SIZE = 25;
    private const ROBUX_FEE = 0.30;
    private const ITEM_URL = '/Item.aspx?id=';
    //TODO: switch to enums soon
    private const STATUS_MAP = ['pending' => 'Open', 'accepted' => 'Finished', 'declined' => 'Declined', 'cancelled' => 'Rejected', 'expired' => 'Expired'];
  
    public function getMyItemTrades(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $validated = $request->validate([
            'statustype' => 'required|string|in:inbound,outbound,completed,inactive',
            'startindex' => 'required|integer|min:0',
        ]);
        $this->expireStaleTrades($userId);
        $query = $this->tradeListQuery($validated['statustype'], $userId);
        $total = (clone $query)->count();
        $trades = $query->with(['sender:id,username', 'receiver:id,username'])->orderByDesc('created_at')->skip((int) $validated['startindex'])->take(self::PAGE_SIZE)->get();
        $rows = $trades->map(function (Trade $trade) use ($userId) {
            $partner = (int) $trade->sender_id === $userId ? $trade->receiver : $trade->sender;
            $expires = $trade->status === 'pending' ? $this->expiryCarbon($trade) : null;
            return json_encode([
                'Date' => Carbon::parse($trade->created_at)->format('M d, Y'),
                'Expires' => $expires ? $expires->format('M d, Y') : '',
                'Status' => self::STATUS_MAP[$trade->status] ?? ucfirst($trade->status),
                'StatusAddon' => '',
                'TradePartner' => $partner->username,
                'TradePartnerID' => (int) $partner->id,
                'TradeSessionID' => (int) $trade->id,
            ]);
        })->values()->all();
        return response()->json(['d' => json_encode([
            'tradeWriteEnabled' => self::TRADING_ENABLED ? 'True' : 'False',
            'Data' => $rows,
            'totalCount' => $total
        ])]);
    }

    public function tradeHandler(Request $request): JsonResponse
    {
        $userId  = (int) $request->user()->id;
        $tradeId = (int) $request->input('TradeID');
        $cmd = (string) $request->input('cmd');
        try {
            switch ($cmd) {
                case 'pull':
                    $trade = $this->findTradeForParticipant($tradeId, $userId);
                    if ($trade->status === 'pending') {
                        $this->expireIfNeeded($trade);
                    }
                    return response()->json(['success' => true, 'data' => $this->buildTradeJson($trade, $userId)]);
                case 'maketrade':
                    $snapshot = $request->input('TradeJSON');
                    $this->performAccept($tradeId, $userId, is_string($snapshot) ? $snapshot : null);
                    return response()->json(['success' => true]);
                case 'decline':
                    $this->closeTrade($tradeId, $userId, 'either');
                    return response()->json(['success' => true]);
                default:
                    throw new DomainException('Unsupported command', 400);
            }
        } catch (DomainException $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'msg' => 'This trade could not be found']);
        }
    }

    public function create(Request $request): JsonResponse
    {
        if (!self::TRADING_ENABLED) {
            abort(503, 'Trading is currently disabled.');
        }
        $userId = (int) $request->user()->id;
        $validated = $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
            'sender_inventory_ids' => 'required|array|min:1|max:' . self::MAX_ITEMS_PER_SIDE,
            'sender_inventory_ids.*' => 'integer',
            'receiver_inventory_ids' => 'required|array|min:1|max:' . self::MAX_ITEMS_PER_SIDE,
            'receiver_inventory_ids.*' => 'integer',
            'sender_robux' => 'sometimes|integer|min:0',
            'receiver_robux' => 'sometimes|integer|min:0'
        ]);
        $receiverId = (int) $validated['receiver_id'];
        if ($receiverId === $userId) {
            abort(422, 'You cannot trade with yourself.');
        }
        $senderIds = array_values(array_unique(array_map('intval', $validated['sender_inventory_ids'])));
        $receiverIds = array_values(array_unique(array_map('intval', $validated['receiver_inventory_ids'])));
        $senderRobux = (int) ($validated['sender_robux'] ?? 0);
        $receiverRobux = (int) ($validated['receiver_robux'] ?? 0);

        try {
            $trade = DB::transaction(function () use ($userId, $receiverId, $senderIds, $receiverIds, $senderRobux, $receiverRobux) {
                $senderItems = $this->lockAndValidateItems($senderIds, $userId);
                $receiverItems = $this->lockAndValidateItems($receiverIds, $receiverId);
                $balances = DB::table('users')->whereIn('id', [$userId, $receiverId])->lockForUpdate()->pluck('robux', 'id');
                if ((int) ($balances[$userId] ?? 0) < $senderRobux) {
                    throw new DomainException('You do not have enough Bytes for this offer', 422);
                }
                if ((int) ($balances[$receiverId] ?? 0) < $receiverRobux) {
                    throw new DomainException('The other user does not have enough Bytes for this offer', 422);
                }
                $trade = Trade::create([
                    'sender_id' => $userId,
                    'receiver_id' => $receiverId,
                    'status' => 'pending',
                    'sender_robux' => $senderRobux,
                    'receiver_robux' => $receiverRobux,
                    'expires_at' => now()->addSeconds(self::TRADE_EXPIRY_SECONDS)->timestamp
                ]);
                foreach ($senderItems as $item) {
                    TradeItem::create(['trade_id' => $trade->id, 'user_id' => $userId, 'inventory_id' => $item->id]);
                    $item->update(['is_locked' => true]);
                }
                foreach ($receiverItems as $item) {
                    TradeItem::create(['trade_id' => $trade->id, 'user_id' => $receiverId, 'inventory_id' => $item->id]);
                    $item->update(['is_locked' => true]);
                }
                return $trade;
            });
        } catch (DomainException $e) {
            abort($e->getCode() ?: 422, $e->getMessage());
        }
        return response()->json(['data' => ['id' => $trade->id]], 201);
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        try {
            $this->performAccept($id, (int) $request->user()->id, null);
        } catch (DomainException $e) {
            abort($e->getCode() ?: 422, $e->getMessage());
        }
        return response()->json(['data' => ['status' => 'accepted']]);
    }

    public function decline(Request $request, int $id): JsonResponse
    {
        try {
            $status = $this->closeTrade($id, (int) $request->user()->id, 'decline');
        } catch (DomainException $e) {
            abort($e->getCode() ?: 422, $e->getMessage());
        }
        return response()->json(['data' => ['status' => $status]]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $status = $this->closeTrade($id, (int) $request->user()->id, 'cancel');
        } catch (DomainException $e) {
            abort($e->getCode() ?: 422, $e->getMessage());
        }
        return response()->json(['data' => ['status' => $status]]);
    }

    private function performAccept(int $tradeId, int $userId, ?string $clientSnapshot): void
    {
        if (!self::TRADING_ENABLED) {
            throw new DomainException('Trading is currently disabled', 503);
        }
        $pre = Trade::where('id', $tradeId)->where('receiver_id', $userId)->where('status', 'pending')->first();
        if (!$pre) {
            throw new DomainException('This trade is no longer available', 404);
        }
        if ($this->expireIfNeeded($pre)) {
            throw new DomainException('This trade has expired', 410);
        }
        DB::transaction(function () use ($tradeId, $userId, $clientSnapshot) {
            $trade = Trade::where('id', $tradeId)->where('receiver_id', $userId)->where('status', 'pending')->lockForUpdate()->first();
            if (!$trade) {
                throw new DomainException('This trade is no longer available', 404);
            }
            $tradeItems = TradeItem::where('trade_id', $trade->id)->get();
            $inventory  = Inventory::whereIn('id', $tradeItems->pluck('inventory_id'))->lockForUpdate()->get()->keyBy('id');
            foreach ($tradeItems as $ti) {
                $inv = $inventory->get($ti->inventory_id);
                if (!$inv || (int) $inv->user_id !== (int) $ti->user_id) {
                    throw new DomainException('One or more items are no longer available', 422);
                }
            }
            if ($clientSnapshot !== null) {
                $this->assertSnapshotMatches($clientSnapshot, $trade, $tradeItems);
            }
            $senderRobux = (int) $trade->sender_robux;
            $receiverRobux = (int) $trade->receiver_robux;
            if ($senderRobux > 0 || $receiverRobux > 0) {
                $balances = DB::table('users')->whereIn('id', [$trade->sender_id, $trade->receiver_id])->lockForUpdate()->pluck('robux', 'id');
                if ((int) ($balances[$trade->sender_id] ?? 0) < $senderRobux) {
                    throw new DomainException('The sender no longer has enough Bytes', 422);
                }
                if ((int) ($balances[$trade->receiver_id] ?? 0) < $receiverRobux) {
                    throw new DomainException('You do not have enough Bytes to complete this trade', 422);
                }
                $net = fn(int $amt) => $amt - (int) ceil($amt * self::ROBUX_FEE);
                if ($senderRobux > 0) {
                    DB::table('users')->where('id', $trade->sender_id)->decrement('moons', $senderRobux);
                    DB::table('users')->where('id', $trade->receiver_id)->increment('moons', $net($senderRobux));
                }
                if ($receiverRobux > 0) {
                    DB::table('users')->where('id', $trade->receiver_id)->decrement('moons', $receiverRobux);
                    DB::table('users')->where('id', $trade->sender_id)->increment('moons', $net($receiverRobux));
                }
            }
            foreach ($tradeItems as $ti) {
                $newOwnerId = (int) $ti->user_id === (int) $trade->sender_id ? $trade->receiver_id : $trade->sender_id;
                Inventory::where('id', $ti->inventory_id)->update(['user_id' => $newOwnerId, 'is_locked' => false]);
            }
            $trade->update(['status' => 'accepted']);
        });
    }

    private function closeTrade(int $tradeId, int $userId, string $mode): string
    {
        return DB::transaction(function () use ($tradeId, $userId, $mode) {
            $trade = Trade::where('id', $tradeId)->where('status', 'pending')->lockForUpdate()->first();
            if (!$trade) {
                throw new DomainException('This trade is no longer available', 404);
            }
            $isReceiver = (int) $trade->receiver_id === $userId;
            $isSender = (int) $trade->sender_id === $userId;
            $allowed = match ($mode) {
                'decline' => $isReceiver,
                'cancel'  => $isSender,
                default   => $isReceiver || $isSender
            };
            if (!$allowed) {
                throw new DomainException('You are not allowed to do that', 403);
            }
            $status = $isReceiver ? 'declined' : 'cancelled';
            $this->releaseTradeItems($trade);
            $trade->update(['status' => $status]);
            return $status;
        });
    }

    private function buildTradeJson(Trade $trade, int $userId): string
    {
        $items = TradeItem::where('trade_id', $trade->id)->with(['inventoryItem.asset'])->get();
        $buildOffer = function (int $ownerId, int $robux) use ($items) {
            $list = $items->where('user_id', $ownerId)->map(function ($ti) {
                $inv = $ti->inventoryItem;
                $asset = $inv->asset ?? null;
                $price = (int) ($asset->robux ?? 0);
                return [
                    'UserAssetID' => (int) $ti->inventory_id,
                    'Name' => $asset->name ?? '',
                    'ItemLink' => $asset ? self::ITEM_URL . $asset->id : '#',
                    'ImageLink' => $asset ? '/Thumbs/Asset.ashx?assetId=' . $asset->id : '',
                    'AveragePrice' => $price,
                    'OriginalPrice' => $price,
                    'SerialNumber' => $inv->serial_number ?? '',
                    'SerialNumberTotal' => $asset->total_serials ?? '',
                    'MembershipLevel'   => null,
                    'AssetID' => (int) ($asset->id ?? 0)
                ];
            })->values();
            return ['AgentID' => $ownerId, 'OfferValue' => (int) $list->sum('AveragePrice'), 'OfferRobux' => $robux, 'OfferList'  => $list->all()];
        };
        $expires = $this->expiryCarbon($trade) ?? now()->addSeconds(self::TRADE_EXPIRY_SECONDS);
        return json_encode([
            'StatusType' => self::STATUS_MAP[$trade->status] ?? ucfirst($trade->status),
            'IsActive' => $trade->status === 'pending',
            'Direction' => (int) $trade->receiver_id === $userId ? 'inbound' : 'outbound',
            'Expiration' => '/Date(' . ($expires->getTimestamp() * 1000) . ')/',
            'AgentOfferList' => [
                $buildOffer((int) $trade->sender_id, (int) $trade->sender_robux),
                $buildOffer((int) $trade->receiver_id, (int) $trade->receiver_robux)
            ]
        ]);
    }

    private function tradeListQuery(string $type, int $userId)
    {
        $query = Trade::query();
        $mine = fn($q) => $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
        return match ($type) {
            'inbound' => $query->where('receiver_id', $userId)->where('status', 'pending'),
            'outbound' => $query->where('sender_id', $userId)->where('status', 'pending'),
            'completed' => $query->where('status', 'accepted')->where($mine),
            'inactive' => $query->whereIn('status', ['declined', 'cancelled', 'expired'])->where($mine)
        };
    }

    private function findTradeForParticipant(int $tradeId, int $userId): Trade
    {
        return Trade::where('id', $tradeId)->where(fn($q) => $q->where('sender_id', $userId)->orWhere('receiver_id', $userId))->with(['sender:id,username', 'receiver:id,username'])->firstOrFail();
    }

    private function expiryCarbon(Trade $trade): ?Carbon
    {
        $raw = $trade->expires_at;
        if ($raw === null || $raw === '') {
            return null;
        }
        if ($raw instanceof \DateTimeInterface) {
            return Carbon::instance($raw);
        }
        return is_numeric($raw) ? Carbon::createFromTimestamp((int) $raw) : Carbon::parse($raw);
    }

    private function expireIfNeeded(Trade $trade): bool
    {
        if ($trade->status !== 'pending') {
            return false;
        }
        $exp = $this->expiryCarbon($trade);
        if ($exp && $exp->isPast()) {
            $this->expireTrade($trade);
            return true;
        }
        return false;
    }

    private function expireStaleTrades(int $userId): void
    {
        Trade::where('status', 'pending')->where(fn($q) => $q->where('sender_id', $userId)->orWhere('receiver_id', $userId))->get()->each(fn(Trade $t) => $this->expireIfNeeded($t));
    }

    private function assertSnapshotMatches(string $json, Trade $trade, Collection $tradeItems): void
    {
        $snap = json_decode($json, true);
        $offers = is_array($snap) ? ($snap['AgentOfferList'] ?? null) : null;
        $changed = new DomainException('This trade has changed. Please review it again', 409);
        if (!is_array($offers) || count($offers) !== 2) {
            throw $changed;
        }
        $expected = [];
        foreach ([[$trade->sender_id, $trade->sender_robux], [$trade->receiver_id, $trade->receiver_robux]] as [$agent, $robux]) {
            $expected[(int) $agent] = [
                'robux' => (int) $robux,
                'ids' => $tradeItems->where('user_id', $agent)->pluck('inventory_id')->map(fn($i) => (int) $i)->sort()->values()->all()
            ];
        }
        foreach ($offers as $offer) {
            $agent = (int) ($offer['AgentID'] ?? 0);
            if (!isset($expected[$agent])) {
                throw $changed;
            }
            $ids = collect($offer['OfferList'] ?? [])->pluck('UserAssetID')->map(fn($i) => (int) $i)->sort()->values()->all();
            if ($ids !== $expected[$agent]['ids'] || (int) ($offer['OfferRobux'] ?? 0) !== $expected[$agent]['robux']) {
                throw $changed;
            }
        }
    }

    private function lockAndValidateItems(array $inventoryIds, int $ownerId)
    {
        $items = Inventory::query()->whereIn('id', $inventoryIds)->where('user_id', $ownerId)->with('asset:id,is_limited,is_limited_unique')->lockForUpdate()->get();
        if ($items->count() !== count($inventoryIds)) {
            throw new DomainException('One or more items are invalid or not owned by the correct user', 422);
        }
        foreach ($items as $item) {
            $asset = $item->asset;
            if (!$asset || (!$asset->is_limited && !$asset->is_limited_unique)) {
                throw new DomainException('Only Limited and Limited Unique items can be traded', 422);
            }
            if ($item->is_locked) {
                throw new DomainException('One or more items are already locked in another trade', 422);
            }
        }
        return $items;
    }

    private function releaseTradeItems(Trade $trade): void
    {
        $inventoryIds = TradeItem::where('trade_id', $trade->id)->pluck('inventory_id');
        Inventory::whereIn('id', $inventoryIds)->update(['is_locked' => false]);
    }

    private function expireTrade(Trade $trade): void
    {
        DB::transaction(function () use ($trade) {
            $this->releaseTradeItems($trade);
            $trade->update(['status' => 'expired']);
        });
    }
}