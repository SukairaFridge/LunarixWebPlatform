<?php
namespace App\Http\Controllers\General\Backend\Renders;
use App\Jobs\Render;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserRender
{
    private const STALE_DAYS = 7;
    private const MAX_BATCH = 25;
    private const THUMB_RENDER_TYPE = 'thumbnail';
    private const THUMB_URL = '/Thumbs/Avatar.ashx?userId=%d&x=%d&y=%d&v=%s';
    private const PROFILE_URL = '/User.aspx?id=%d';
    private const THUMB_SIZES = ['tiny' => 48, 'small' => 100, 'medium' => 150, 'large' => 352];
    public function redirectRender(Request $request, string $renderType)
    {
        $placeholder = '/img/ph/user_placeholder.png';
        if (config('app.lunarix_renders_disabled', false)) {
            return redirect()->away($placeholder);
        }
        $userId = (int) $request->query('userId');
        if (! $userId) {
            return response()->json(['success' => false, 'error' => 'Missing userId'], 400);
        }
        $typeMap = ['avatar' => 'thumbnail', 'headshot' => 'closeup'];
        $renderTypeKey = strtolower($renderType);
        if (! isset($typeMap[$renderTypeKey])) {
            return response()->json(['success' => false, 'error' => 'Invalid render type'], 400);
        }
        $mappedType = $typeMap[$renderTypeKey];
        $latest = DB::table('user_renders')->where('user_id', $userId)->where('render_type', $mappedType)->orderByDesc('created_at')->first();
        $isStale = $latest ? \Carbon\Carbon::parse($latest->created_at, config('app.timezone'))->lessThan(now()->subDays(7)) : true;
        $isOutdated = $latest ? (bool) $latest->outdated : true;
        $needsNewRender = ! $latest || $isOutdated || $isStale;
        if ($needsNewRender) {
            $lockKey = "render_pending:{$userId}:{$mappedType}";
            if (! Cache::has($lockKey)) {
                Cache::put($lockKey, true, now()->addSeconds(180));
                Render::dispatch($userId, 2015, $mappedType);
            }
        }
        if (! $latest || $isOutdated || $isStale) {
            return redirect()->away($placeholder);
        }
        $render = $latest;
        $path = trim($render->render_path);
        $path = preg_replace('#^https?://[^/]+/#i', '', $path);
        $path = preg_replace('#^(?:Thumbs/)?synvo\.live/#i', '', $path);
        $path = ltrim($path, '/');
        $disk = Storage::disk('renders');
        if (! $disk->exists($path)) {
            return redirect()->away($placeholder);
        }
        $imageData = $disk->get($path);
        $targetW = $request->query('x') !== null ? (int) $request->query('x') : null;
        $targetH = $request->query('y') !== null ? (int) $request->query('y') : null;
        if ($targetW === null || $targetH === null) {
            $mime = $disk->mimeType($path) ?: 'image/png';
            return response($imageData, 200)->header('Content-Type', $mime)->header('Cache-Control', 'public, max-age=86400')->header('X-Render-Path', $path);
        }
        $src = imagecreatefromstring($imageData);
        if ($src === false) {
            return redirect()->away($placeholder);
        }
        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $dst = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        if ($renderTypeKey === 'headshot') {
            $cropW = 200;
            $cropH = 200;
            $offsetX = 390;
            $offsetY = 130;
            $srcX = max(0, $offsetX - ($cropW / 2));
            $srcY = max(0, $offsetY);
            $dstX = max(0, -$offsetX);
            $dstY = max(0, -$offsetY);
            $copyW = min($cropW - abs($dstX), $srcW - $srcX);
            $copyH = min($cropH - abs($dstY), $srcH - $srcY);
            imagecopyresampled($dst, $src, $dstX, $dstY, $srcX, $srcY, $copyW, $copyH, $copyW, $copyH);
        } else {
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);
        }
        ob_start();
        imagepng($dst);
        $imageData = ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);
        return response($imageData, 200)->header('Content-Type', 'image/png')->header('Cache-Control', 'public, max-age=86400')->header('X-Render-Path', $path);
    }

    public function avatarThumbnails(Request $request)
    {
        $callback = (string) $request->query('jsoncallback', '');
        if ($callback !== '' && ! preg_match('/^[A-Za-z_$][A-Za-z0-9_$.]{0,100}$/', $callback)) {
            return response()->json(['error' => 'Invalid callback'], 400);
        }
        $params = json_decode((string) $request->query('params', '[]'), true);
        if (! is_array($params)) {
            return $this->jsonpResponse([], $callback);
        }
        $params = array_slice(array_values($params), 0, self::MAX_BATCH);
        $ids = [];
        foreach ($params as $p) {
            $id = is_array($p) ? (int) ($p['userId'] ?? 0) : 0;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        $ids = array_values($ids);
        $usernames = $ids ? DB::table('users')->whereIn('id', $ids)->pluck('username', 'id') : collect();
        $renders = $ids ? $this->latestThumbnailRenders($ids) : collect();
        $disk = Storage::disk('renders');
        $out = [];
        foreach ($params as $p) {
            $userId = is_array($p) ? (int) ($p['userId'] ?? 0) : 0;
            if ($userId <= 0 || ! isset($usernames[$userId])) {
                $out[] = null;
                continue;
            }
            $size = self::THUMB_SIZES[strtolower((string) ($p['imageSize'] ?? 'medium'))] ?? self::THUMB_SIZES['medium'];
            $render = $renders->get($userId);
            $final = $this->thumbnailIsFinal($render, $disk);
            $version = $final ? \Carbon\Carbon::parse($render->created_at)->timestamp : time();
            $out[] = ['url' => sprintf(self::PROFILE_URL, $userId), 'name' => $usernames[$userId], 'thumbnailUrl' => sprintf(self::THUMB_URL, $userId, $size, $size, $version), 'thumbnailFinal' => $final, 'bcOverlayUrl' => null];
        }
        return $this->jsonpResponse($out, $callback);
    }

    private function latestThumbnailRenders(array $ids)
    {
        $latest = DB::table('user_renders')->select('user_id', DB::raw('MAX(created_at) as max_created'))->whereIn('user_id', $ids)->where('render_type', self::THUMB_RENDER_TYPE)->groupBy('user_id');
        return DB::table('user_renders as r')->joinSub($latest, 'm', fn ($j) => $j->on('r.user_id', '=', 'm.user_id')->on('r.created_at', '=', 'm.max_created'))->where('r.render_type', self::THUMB_RENDER_TYPE)->select('r.*')->get()->keyBy('user_id');
    }

    private function thumbnailIsFinal($render, $disk): bool
    {
        if (! $render || (bool) $render->outdated) {
            return false;
        }
        if (\Carbon\Carbon::parse($render->created_at, config('app.timezone'))->lessThan(now()->subDays(self::STALE_DAYS))) {
            return false;
        }
        $path = trim((string) $render->render_path);
        $path = preg_replace('#^https?://[^/]+/#i', '', $path);
        $path = preg_replace('#^(?:Thumbs/)?synvo\.live/#i', '', $path);
        $path = ltrim($path, '/');
        return $path !== '' && $disk->exists($path);
    }

    private function jsonpResponse(array $data, string $callback)
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
        if ($callback === '') {
            return response($json, 200, ['Content-Type' => 'application/json']);
        }
        return response('/**/' . $callback . '(' . $json . ');', 200, ['Content-Type' => 'text/javascript; charset=utf-8', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'no-store']);
    }
}