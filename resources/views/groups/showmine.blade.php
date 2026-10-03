@extends('layout.root')
@push('css')
<link rel="stylesheet" href="/CSS/Base/CSS/FetchCSS?path=main___23e1cfc4f7f34bdc2ff4f3c2d637a122_m.css">
<link rel="stylesheet" href="/CSS/Base/CSS/FetchCSS?path=page___9a4182a4485f796c2e3a9036b8bbaca8_m.css">
@endpush
@section('content')
@include('layout.header')
        <div id="navContent" class="nav-content"><div class="nav-content-inner">
    <div id="MasterContainer" >
<script type='text/javascript' src='https://js.rbxcdn.com/ec43bbe2464c7dead5f4c5f723965c3a.js.gzip'></script>
<script type="text/javascript">
    $(function(){
        function trackReturns() {
            function dayDiff(d1, d2) {
                return Math.floor((d1-d2)/86400000);
            }
            if (!localStorage) {
                return false;
            }

            var cookieName = 'RBXReturn';
            var cookieOptions = {expires:9001};
            var cookieStr = localStorage.getItem(cookieName) || "";
            var cookie = {};

            try {
                cookie = JSON.parse(cookieStr);
            } catch (ex) {
                // busted cookie string from old previous version of the code
            }

            try {
                if (typeof cookie.ts === "undefined" || isNaN(new Date(cookie.ts))) {
                    localStorage.setItem(cookieName, JSON.stringify({ ts: new Date().toDateString() }));
                    return false;
                }
            } catch (ex) {
                return false;
            }

            var daysSinceFirstVisit = dayDiff(new Date(), new Date(cookie.ts));
            if (daysSinceFirstVisit == 1 && typeof cookie.odr === "undefined") {
                RobloxEventManager.triggerEvent('rbx_evt_odr', {});
                cookie.odr = 1;
            }
            if (daysSinceFirstVisit >= 1 && daysSinceFirstVisit <= 7 && typeof cookie.sdr === "undefined") {
                RobloxEventManager.triggerEvent('rbx_evt_sdr', {});
                cookie.sdr = 1;
            }
            try {
                localStorage.setItem(cookieName, JSON.stringify(cookie));
            } catch (ex) {
                return false;
            }
        }

        GoogleListener.init();

        RobloxEventManager.initialize(true);
        RobloxEventManager.triggerEvent('rbx_evt_pageview');
        trackReturns();

        RobloxEventManager._idleInterval = 450000;
        RobloxEventManager.registerCookieStoreEvent('rbx_evt_initial_install_start');
        RobloxEventManager.registerCookieStoreEvent('rbx_evt_ftp');
        RobloxEventManager.registerCookieStoreEvent('rbx_evt_initial_install_success');
        RobloxEventManager.registerCookieStoreEvent('rbx_evt_fmp');
        RobloxEventManager.startMonitor();
    });
</script>

        <script type="text/javascript">Roblox.FixedUI.gutterAdsEnabled=false;</script>

        <div id="Container">
        </div>

            <div id="AdvertisingLeaderboard">
    <iframe allowtransparency="true" frameborder="0" height="110" scrolling="no" src="https://www.roblox.com/userads/1" width="728" data-js-adtype="iframead"></iframe>
            </div>

        <noscript><div class="SystemAlert"><div class="SystemAlertText">Please enable Javascript to use all the features on this site.</div></div></noscript>

        <div id="BodyWrapper">

            <div id="RepositionBody">
                <div id="Body" style='width:970px;'>

    <style type="text/css">
        #Body {
            padding: 10px;
            width: 970px;
        }
    </style>

<script type="text/javascript">
     Roblox.Clans = Roblox.Clans || {};
     //<sl:translate>
     Roblox.Clans.Resources = {
        BuyClanTitle: "Purchase Clan",
        BuyClanMessage: "Purchasing a Clan will cost <span class='robux'>{0}</span>. Do you want to continue?<p class='use-group-funds invisible'><input type='checkbox' /> Use Group funds to purchase a Clan</p>",
        BuyClanAcceptText: "Buy Now",
        BuyClanCancelText: "Cancel",
        PersonalBalanceAfterText: "Your balance after this transaction will be ",
        GroupBalanceAfterText: "Your group's balance after this transaction will be ",
        BuyClanNotEnoughMoneyText: "You need {0} more ROBUX to purchase a Clan. <a href='/upgrades/robux'>Purchase ROBUX now</a>!",
        ErrorTitle: "Error",
        SuccessTitle: "Success!",
        SuccessClanInvitationText: "Your invitation has been sent.",
        ClanInviteTitle : "Invite To {0}",
        ClanInviteMessage : "Do you want to join this Clan?",
        ClanInviteSubMessage : "You may only be in one Clan at a time. You will be removed from your current Clan.",
        ClanInviteAcceptText : "Accept",
        ClanInviteCancelText : "Decline",
        CreateInviteTitle: "Invite to Clan",
        CreateInviteText: "Invite this user to your Clan?",
        ClanKickTitle: "Kick from Clan",
        ClanKickText: "Are you sure you want to kick this user from your Clan? They will remain a member of the group.",
        ClanKickSuccessText: "Clan kick successful.",
        LeaveClanTitle: "Leave Clan",
        LeaveClanText: "Are you sure you want to leave the Clan?",
        LeaveClanSuccessText: "You are no longer in the Clan.",
        CancelInviteTitle: "Cancel Clan Invitation",
        CancelInviteText: "Are you sure you want to cancel this pending Clan invitation?",
        CancelInviteSuccessText: "The Clan invitation has been cancelled.",
        AdminJoinClanTitle: "Join Clan",
        AdminJoinClanText: "Join this Clan? <span class='already-in-clan invisible'>You will automatically leave your current Clan.</span>"
     };
 //</sl:translate>
 </script>

<div class="GenericModal modalPopup unifiedModal smallModal" style="display:none;">
    <div class="Title"></div>
    <div class="GenericModalBody">
        <div>
            <div class="ImageContainer">
                <img class="GenericModalImage" alt="generic image"/>
            </div>
            <div class="Message"></div>
        </div>
        <div class="clear"></div>
        <div id="GenericModalButtonContainer" class="GenericModalButtonContainer">
            <a class="ImageButton btn-neutral btn-large roblox-ok">OK</a>
        </div>
    </div>
</div>

@if ($group)
<input name="__RequestVerificationToken" type="hidden" value="{{ csrf_token() }}" /><div id="ClanInvitationData"
     data-is-invitation-pending="False"
     data-group-id="{{ $group->id }}"
     data-is-in-other-clan="False"
     data-group-name="{{ $group->name }}"></div>
@endif

            <div id="left-column">
                <div class="GroupListContainer StandardBox">
                    @if ($myGroups->count() >= 5)
                    <div id="ctl00_cphRoblox_CreateNewGroup" disabled="disabled" title="You may only be in a maximum of 5 groups at one time">
                        <div id="CreateGroupBtn" class="btn-large btn-disabled-primary" style="color:#fff;">
                            Create
                        </div>
                    </div>
                    @else
                    <div id="ctl00_cphRoblox_CreateNewGroup">
                        <a href="/My/CreateGroup.aspx" id="CreateGroupBtn" class="btn-large btn-primary">
                            Create
                        </a>
                    </div>
                    @endif

<div id="GroupThumbnails" >
    <div id="ctl00_cphRoblox_MyGroupsPane_SmallThumbsPanel" class="CarouselPager">
        @foreach ($myGroups as $g)
                <div class="GroupListItemContainer {{ $group && $group->id === $g->id ? 'selected' : '' }}">
                    <div class="GroupListImageContainer notranslate">
                        <a title="{{ $g->name }}" href="/My/Groups.aspx?gid={{ $g->id }}" style="display:inline-block;height:42px;width:42px;cursor:pointer;"><img src="{{ $group->icon_url ?? '/images/default-group-icon.png' }}" height="42" width="42" border="0" alt="{{ $g->name }}" /></a>
                    </div>
                    <div class="GroupListName notranslate">{{ $g->name }}</div>
                    <div style="clear:both;"></div>
                </div>
        @endforeach
    </div>
    <div style="clear:both;"></div>
</div>

<script type="text/javascript">
    $(function() {
        $(".GroupListImageContainer").find('a').each(function (index, elem) { $(elem).attr('title', $(elem).attr('title').replace(new RegExp("&quot;", "gm"), '"')); });
    });
</script>
                </div>
            </div>
            <div id="mid-column">

<div id="SearchControls">
    <form class="content" method="get" action="/Groups/Groups.aspx">
        <input name="keyword" type="text" id="ctl00_cphRoblox_GroupSearchBar_SearchKeyword" onclick="if(this.value == 'Search all groups'){ this.value = ''};$(this).removeClass('default');" class="SearchKeyword default translate" maxlength="100" value="Search all groups" />
        <input type="submit" value="Search" onclick="if ($get(SearchKeywordText).value == '' || $get(SearchKeywordText).value == 'Search all groups') return false;" id="ctl00_cphRoblox_GroupSearchBar_SearchButton" class="group-search-button translate" />
        <input type="text" style="visibility: hidden; position: absolute">
    </form>
</div>

<script type="text/javascript">
    var SearchKeywordText = 'ctl00_cphRoblox_GroupSearchBar_SearchKeyword';
</script>

@if (! $group)
                <div id="description">
                    <div class="GroupPanelContainer">
                        <div style="text-align:center;padding:40px 10px;">
                            <h2>You are not in any groups</h2>
                            <p>Use the search box above to find a group to join.</p>
                        </div>
                    </div>
                </div>
@else
                <div id="description">
                    <div class="GroupPanelContainer">
                        <div class="left-col">
                            <div class="GroupThumbnail">
                                <a title="{{ $group->name }}" style="display:inline-block;cursor:pointer;"><img src="{{ $group->icon_url ?? '/images/groups/default.png' }}" height="150" width="150" border="0" alt="{{ $group->name }}" /></a>
                            </div>
                            <div class="GroupOwner">
                                <div id="ctl00_cphRoblox_OwnershipPanel">
	Owned By: @if ($group->owner)<a style="font-style: italic;" href="/users/{{ $group->owner->id }}/profile">{{ $group->owner->username }}</a>@endif
</div>
                                <div id="MemberCount">Members: {{ $memberCount }}</div>
                                <div id="ClanMemberCount">Clan Members: <span class="clan-members-count">{{ $clanMembers->count() }}</span></div>
                            </div>
                            <div class="MyRank">
                                <div>My Rank: <span id="ctl00_cphRoblox_StatusRank" class="notranslate">{{ $membership->role->name ?? '' }}</span></div>
                            </div>
                        </div>
                        <div class="right-col">
                            <h2 class="notranslate">{{ $group->name }}</h2>
                            <div id="GroupDescP" class="linkify">
                                <pre class="notranslate">{{ $group->description }}</pre>
                            </div>
                            <input type="hidden" id="GroupDesc_Full" class="notranslate" value="{{ $group->description }}" />
                            <div id="ctl00_cphRoblox_AbuseReportButton_AbuseReportPanel" class="ReportAbuse">
    <span class="AbuseIcon"><a href="/abusereport/group?id={{ $group->id }}&RedirectUrl={{ urlencode('/my/groups.aspx?gid='.$group->id) }}"><img src="../images/abuse.PNG?v=2" alt="Report Abuse" style="border-width:0px;" /></a></span>
    <span class="AbuseButton"><a href="/abusereport/group?id={{ $group->id }}&RedirectUrl={{ urlencode('/my/groups.aspx?gid='.$group->id) }}">Report Abuse</a></span>
</div>
                            <br />
                            @if ($group->latestStatus)
                            <div id="ctl00_cphRoblox_GroupStatusPane_status">
    <div id="ctl00_cphRoblox_GroupStatusPane_StatusView" class="StatusView ">
        <div class="top" >
            <span id="ctl00_cphRoblox_GroupStatusPane_StatusTextField" class="StatusTextField linkify">{{ $group->latestStatus->status }}</span>
        </div>
        <div class="bottom">
            <div class="content">
                @if ($group->latestStatus->author)
                <a id="ctl00_cphRoblox_GroupStatusPane_StatusPoster" href="/users/{{ $group->latestStatus->author->id }}/profile" style="font-style: italic;">{{ $group->latestStatus->author->username }}</a>
                @endif
                <span style="color: #808080; font-size: 8px"><span id="ctl00_cphRoblox_GroupStatusPane_StatusDate">{{ $group->latestStatus->created_at->format('n/j/Y g:i:s A') }}</span></span>
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
</div>
                            @endif
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </div>
                <div id="GroupsPeopleContainer" class="divider-bottom">

                    <div>
                            <div id="GroupsPeople_Games" class="tab active">Games</div>

                            <div id="GroupsPeople_Clan" class="tab ">Clan</div>

                        <div id="GroupsPeople_Members" class="tab ">Members</div>
                        <div id="GroupsPeople_Allies" class="tab">Allies</div>
                        <div id="GroupsPeople_Enemies" class="tab">Enemies</div>
                        <div id="GroupsPeople_Items" class="tab">Store</div>

                        <div style="clear: both;"></div>
                    </div>

                    <div id="GroupsPeople_Pane">

                            <div id="GroupsPeoplePane_Games" class="tab-content" style="display: block">
<div class="results-container" data-page="1" data-group-id="{{ $group->id }}">
    <p>This group has no games.</p>
    <div class="clear"></div>
</div>
                            </div>

                        <div id="GroupsPeoplePane_Clan" class="tab-content" style="display: none;">
                            @foreach ($clanMembers as $cm)
                                @if ($cm->user)
    <div class="GroupMember">
        <div class="Avatar">
            <a class="roblox-avatar-image" data-user-id="{{ $cm->user->id }}" data-image-size="small" href="/users/{{ $cm->user->id }}/profile"></a>
        </div>
        <div class="Summary">
            <span class="Name">
                <a href="/users/{{ $cm->user->id }}/profile" class="NameText notranslate" title="{{ $cm->user->username }}">{{ $cm->user->username }}</a>
            </span>
        </div>
    </div>
                                @endif
                            @endforeach

<div class="clear"></div>

<input type="hidden" class="get-clan-members-data" data-results-per-page="10" data-group-id="{{ $group->id }}"/>
                        </div>

                        <div id="GroupsPeoplePane_Members" class="tab-content" style="display: none">

<div id="GroupRoleSetsMembersPane" >
    <div id="ctl00_cphRoblox_rbxGroupRoleSetMembersPane_GroupMembersUpdatePanel" Class="MembersUpdatePanel">

            <div>
                <div class="Members_DropDown">
                    <select id="ctl00_cphRoblox_rbxGroupRoleSetMembersPane_dlRolesetList" onchange="window.location='/My/Groups.aspx?gid={{ $group->id }}&role='+this.value" class="MembersDropDownList" style="max-width: 100%">
                        @foreach ($roles as $r)
		<option value="{{ $r->id }}" @selected($activeRole && $activeRole->id === $r->id)>{{ $r->name }}</option>
                        @endforeach
	</select>
                </div>
                <div>
                    <div class="spacer" style="visibility:hidden;display:block;height:69px;width:1px;float:left;"></div>

                    @if ($members)
                        @foreach ($members as $m)
                            @if ($m->user)
                            <div class="GroupMember" >
				                <div class="Avatar">
					                <a class=" notranslate" title="{{ $m->user->username }}" href="/users/{{ $m->user->id }}/profile" style="display:inline-block;height:100px;width:100px;cursor:pointer;"><img src="/Thumbs/Avatar.ashx?userId={{ $m->user->id }}" height="100" width="100" border="0" alt="{{ $m->user->username }}" class=" notranslate" /></a>
                                </div>
				                <div class="Summary" >
					                <span class="Name"><a title="{{ $m->user->username }}" class="NameText notranslate" href="/users/{{ $m->user->id }}/profile">{{ $m->user->username }}</a></span>
				                </div>
                            </div>
                            @endif
                        @endforeach

                        <div style="clear:both;"></div>
                    <div style="clear:both;"></div>
                  <div class="FooterPager">
	                    <span><span class="pagerbtns previous"> </span>&nbsp;
                                <div class="paging_wrapper">
                                    Page <input type="text" value="{{ $members->currentPage() }}" class="paging_input" onkeydown="if(event.key==='Enter'){window.location='{{ $members->url(1) }}'.replace(/membersPage=\d+/,'membersPage='+this.value);}" /> of
                                    <div class="paging_pagenums_container" >{{ $members->lastPage() }}</div>
                                </div>
                                <a class="pagerbtns next" @if ($members->hasMorePages()) href="{{ $members->nextPageUrl() }}" @endif> </a>&nbsp;</span>
                    </div>
                    @endif
                </div>
            </div>
    </div>
    <div style="clear: both"></div>
</div>

                        </div>
                        <div id="GroupsPeoplePane_Allies" class="tab-content">
                            <div class="grouprelationshipscontainer">
        <div >
            @forelse ($allies as $a)
                    <div style="width:42px;height:42px;padding:8px;float:left">
                        <a alt="{{ $a->name }}" title="{{ $a->name }}" href="/Groups/group.aspx?gid={{ $a->id }}" style="display:inline-block;height:42px;width:42px;cursor:pointer;"><img src="{{ $a->icon_url ?? '/images/default-group-icon.png' }}" height="42" width="42" border="0" alt="{{ $a->name }}" /></a>
                    </div>
            @empty
            @endforelse
                    <div style="clear:both;margin-bottom:10px;"></div>
            <div style="text-align:center">
            </div>
        </div>
</div>
                        </div>
                        <div id="GroupsPeoplePane_Enemies" class="tab-content">
                            <div class="grouprelationshipscontainer">
        <div >
            @foreach ($enemies as $e)
                    <div style="width:42px;height:42px;padding:8px;float:left">
                        <a alt="{{ $e->name }}" title="{{ $e->name }}" href="/Groups/group.aspx?gid={{ $e->id }}" style="display:inline-block;height:42px;width:42px;cursor:pointer;"><img src="{{ $e->icon_url ?? '/images/default-group-icon.png' }}" height="42" width="42" border="0" alt="{{ $e->name }}" /></a>
                    </div>
            @endforeach
                    <div style="clear:both;margin-bottom:10px;"></div>
        </div>
</div>
                        </div>
                        <div id="GroupsPeoplePane_Items" class="tab-content">
<div id="GroupItemPaneInstructions">
    <p>Groups have the ability to create and sell official shirts, pants, and t-shirts! All revenue goes to group funds.</p>
</div>
<div id="GroupItemContent">
    <div id="GroupItemPaneContent">
        @if ($storeItems->isEmpty())
            <p>This group has no items.</p>
        @endif
    </div>
    <div style="clear:both;text-align: center;padding-top:25px;">
        <a href="/catalog/browse.aspx?IncludeNotForSale=false&SortType=3&CreatorID={{ $group->id }}">See more items for sale by this group</a>
    </div>
</div>
                        </div>

                    </div>
                </div>

<script type="text/javascript">
    Roblox.ExileModal.InitializeGlobalVars({{ $activeRole->id ?? 0 }}, {{ $group->id }});
</script>

<div id="ctl00_cphRoblox_GroupWallPane_Wall">
    <div class="StandardBox" style="margin-bottom: 0px;border-bottom:none;" >
        <span class="InsideBoxHeader">Wall</span>
        @if ($canWallPost)
        <form id="WallPostBox" method="post" action="/My/Groups.aspx?gid={{ $group->id }}">
            @csrf
            <input type="hidden" name="action" value="post_wall">
            <textarea name="content" rows="2" cols="20" id="ctl00_cphRoblox_GroupWallPane_NewPost" class="GroupWallPostText" style="width:85%;" maxlength="1000" required>{{ old('content') }}</textarea>
         <input type="submit" value="Post" id="ctl00_cphRoblox_GroupWallPane_NewPostButton" class="btn-control btn-control-large GroupWallPostBtn translate" />
        </form>
        @endif
        <div style="clear:both;"></div>
    </div>
    <div class="StandardBox GroupWallPane" style="background:#fff;border-top:none;">
        <div id="ctl00_cphRoblox_GroupWallPane_GroupWallUpdatePanel">

    <div id="ExileModal" class="modalPopup blueAndWhite" style="width: 380px; min-height: 50px; display: none;">
		<div id="Div4" class="simplemodal-close">
            <a class="ImageButton closeBtnCircle_35h" style="cursor: pointer; margin-left:385px; position:absolute; top:-18px; left:-10px;"></a>
        </div>
        <div style="text-align:center;">
            <div  class="titleBar">Warning!</div>
            <div style="text-align: center; padding: 10px">
		        <p>
		            Are you sure you want to exile this user?
                    <span class="exile-user-clan-kick-message invisible">They will also be kicked from the Clan.</span>
                </p>
		        <div style="text-align: center; margin: 5px 10px 0px 0px">
		            <a onclick="Roblox.ExileModal.Exile();" id="ctl00_cphRoblox_GroupWallPane_ContinueExile" class="btn-neutral btn-medium" style="cursor:pointer;"><span>Exile</span></a>
                    <a onclick="Roblox.ExileModal.Close();" id="ctl00_cphRoblox_GroupWallPane_CancelExile" class="btn-negative btn-medium" style="cursor: pointer"><span>Cancel</span></a>
                    <p><input type="checkbox" id="deleteAllPostsByUser" value="1" name="deleteAllPostsByUser" onclick="Roblox.ExileModal.SetDeletePostsBool();"/> Also delete all posts by this user. (Please allow some time.)</p>
                </div>
            </div>
        </div>
    </div>

                @foreach ($wallPosts as $post)
                        <div class="{{ $loop->odd ? 'AlternatingItemTemplateOdd' : 'AlternatingItemTemplateEven' }}">
                            <div class="RepeaterImage" >
                                @if ($post->author)
                                <a class=" notranslate" title="{{ $post->author->username }}" href="/users/{{ $post->author->id }}/profile" style="display:inline-block;height:100px;width:100px;cursor:pointer;"><img src="/Thumbs/Avatar.ashx?userId={{ $post->author->id }}" height="100" width="100" border="0" alt="{{ $post->author->username }}" class=" notranslate" /></a>
                                @endif
                            </div>
                            <div class="RepeaterText" >
                                <div class="GroupWall_PostContainer notranslate linkify">
                                    {{ $post->content }}
                                </div>
                                <div>
                                    <div class="GroupWall_PostDate">
                                        <span style="color: Gray;">{{ $post->created_at->format('n/j/Y g:i:s A') }}</span>
                                        by
                                        @if ($post->author)
                                        <span class="UserLink notranslate">
                                            <a href="/users/{{ $post->author->id }}/profile">
                                                {{ $post->author->username }}
                                            </a>
                                        </span>
                                        @endif
                                        <div style="float: right">
                                            <div class="ReportAbuse">
    <span class="AbuseIcon"><a href="/abusereport/groupwallpost?id={{ $post->id }}&RedirectUrl={{ urlencode('/my/groups.aspx?gid='.$group->id) }}"><img src="../images/abuse.PNG?v=2" alt="Report Abuse" style="border-width:0px;" /></a></span>
    <span class="AbuseButton"><a href="/abusereport/groupwallpost?id={{ $post->id }}&RedirectUrl={{ urlencode('/my/groups.aspx?gid='.$group->id) }}">Report Abuse</a></span>
	</div>
                                        </div>
                                    </div>
                                    <div class="GroupWall_PostBtns" style="min-height:0">
                                    </div>
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                       </div>
                @endforeach

                        <div style="clear:both;"></div>
                <div style="clear:both;"></div>
                <div class="FooterPager">
	                <span><span class="pagerbtns previous"> </span>&nbsp;
                                        <div class="paging_wrapper">
                                            Page <input type="text" value="{{ $wallPosts->currentPage() }}" class="paging_input" /> of
                                            <div class="paging_pagenums_container" >{{ $wallPosts->lastPage() }}</div>
                                        </div>
                                <a class="pagerbtns next" @if ($wallPosts->hasMorePages()) href="{{ $wallPosts->nextPageUrl() }}" @endif> </a>&nbsp;</span>
                </div>

</div>
    </div>
</div>
@endif

            </div>
            <div id="right-column">

                @if ($group)
                <div class="GroupControlsBox">
                    <span class="InsideBoxHeader">Controls</span>

                    <div id="AdminOptions">
                    </div>
                    <div id="ctl00_cphRoblox_ManagePrimaryGroup">
                        <form method="post" action="/My/Groups.aspx?gid={{ $group->id }}">
                            @csrf
                            @if ($isPrimaryGroup)
                            <input type="hidden" name="action" value="remove_primary">
                            <input type="submit" value="Remove Primary" onclick="return confirm('Are you sure you want to remove this as your primary group?');" id="ctl00_cphRoblox_MakePrimaryGroupButton" class="btn-control btn-control-medium translate" />
                            @else
                            <input type="hidden" name="action" value="make_primary">
                            <input type="submit" value="Make Primary" onclick="return confirm('Are you sure you want to make this your primary group?');" id="ctl00_cphRoblox_MakePrimaryGroupButton" class="btn-control btn-control-medium translate" />
                            @endif
                        </form>
                    </div>

                    <div id="LeaveGroup">
                        <form method="post" action="/My/Groups.aspx?gid={{ $group->id }}">
                            @csrf
                            <input type="hidden" name="action" value="leave">
                            <input type="submit" value="Leave Group" onclick="return confirm('Are you sure you\'d like to leave this group?');" id="ctl00_cphRoblox_LeaveButton" class="btn-control btn-control-medium translate" />
                        </form>
                    </div>

                </div>
                @endif
                <div id="ad" style="height: 600px">
    <iframe allowtransparency="true" frameborder="0" height="612" scrolling="no" src="https://www.roblox.com/userads/2" width="160" data-js-adtype="iframead"></iframe>
                </div>
            </div>

    <br style="clear: both" />

    <div id="GetBC" class="modalPopup blueAndWhite" style="width: 380px; min-height: 50px; display: none; position: absolute; top: -200px;">
        <div id="Div2" class="simplemodal-close">
            <a class="ImageButton closeBtnCircle_35h" style="cursor: pointer; margin-left: 385px; position: absolute; top: -18px; left: -10px;"></a>
        </div>
        <div style="padding: 0px 0px 15px 0px; text-align: center;">
            <div class="titleBar">
                Uh-Oh!
            </div>
            <div>
                <p>Creating a group requires a Builders Club membership.</p>
                <div style="text-align: center; margin: 5px 10px 0px 0px">
                    <a class="btn-neutral btn-medium" style="cursor: pointer" href="https://www.roblox.com/premium/membership"><span>Get BC!</span></a>
                    <a class="btn-negative btn-medium" onclick="GetBC.close();return false;" style="cursor: pointer"><span>Cancel</span></a>
                </div>
            </div>
        </div>
    </div>

    <div id="LeaveGroupAsOwner" class="modalPopup blueAndWhite" style="width: 380px; min-height: 50px; display: none; position: absolute; top: -200px;">
        <div id="Div1" class="simplemodal-close">
            <a class="ImageButton closeBtnCircle_35h" style="cursor: pointer; margin-left: 385px; position: absolute; top: -18px; left: -10px;"></a>
        </div>
        <div style="padding: 0px 0px 15px 0px; text-align: center;">
            <div class="titleBar">
                Warning!
            </div>
            <p>You are the owner of this group.  Leaving the group will allow any Builders Club member to claim ownership.</p>
            <p>You will lose all of the privileges of group ownership.</p>
            <div style="text-align: center; margin: 5px 10px 0px 0px">
                <a id="ctl00_cphRoblox_LinkButton3" class="btn-neutral btn-medium" style="cursor: pointer"><span>Continue</span></a>
                <a onclick="LeaveGroupAsOwner.close();" id="ctl00_cphRoblox_LinkButton4" class="btn-negative btn-medium" style="cursor: pointer"><span>Cancel</span></a>
            </div>

        </div>
    </div>
    <script type="text/javascript">

        if (typeof Roblox === "undefined") {
            Roblox = {};
        }

        if (typeof Roblox.Resources === "undefined") {
            Roblox.Resources = {};
        }
        Roblox.Resources.more = "More";
        Roblox.Resources.less = "Less";

        var GetBC = {};
        var LeaveGroupAsOwner = {};
        GetBC.close = function () {
            $.modal.close(".GetBC");
        };

        GetBC.open = function () {
            var modalProperties = { overlayClose: true, escClose: true, opacity: 80, overlayCss: { backgroundColor: "#000" } };
            $("#GetBC").modal(modalProperties);
        };

        LeaveGroupAsOwner.close = function () {
            $.modal.close(".LeaveGroupAsOwner");
        };

        $(function () {
            var modalProperties = { overlayClose: true, escClose: true, opacity: 80, overlayCss: { backgroundColor: "#000" } };
        });

    </script>

                    <div style="clear:both"></div>
                </div>
            </div>
        </div>
@include('layout.footerlegacy')
@endsection