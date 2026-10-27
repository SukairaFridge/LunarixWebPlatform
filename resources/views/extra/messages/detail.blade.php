@verbatim
<div>
    <div class="messages message-detail">
        <div class="message-confirm status-confirm neurotic-message-confirm"
             ng-show="sendMessage.sendComplete == true && sendMessage.sendResult.hasError == false ">
            {{sendMessage.sendResult.details}}
        </div>
        <div class="message-confirm status-error neurotic-message-confirm"
             ng-show="sendMessage.sendComplete == true && sendMessage.sendResult.hasError == true"
             ng-bind-html="sendMessage.sendResult.details">

        </div>
        <div class="clearfix">
            <div class="sender">
                <div class="neurotic-avatar-image message-detail-image neurotic-message-avatar">
                    <a ng-if="currentStatus.activeTab != 'sent'"
                       ng-href="{{selectedMessage.SenderAbsoluteUrl}}"
                       lrx-avatar
                       thumbnail="selectedMessage.SenderThumbnail | isEmpty : messageDefaults.neuroticUserThumbnail:selectedMessage.Sender.UserId">
                    </a>
                    <a ng-if="currentStatus.activeTab == 'sent'"
                       ng-href="{{selectedMessage.RecipientAbsoluteUrl}}"
                       lrx-avatar
                       thumbnail="selectedMessage.RecipientThumbnail">
                    </a>
                </div>
            </div>
            <div class="subject neurotic-send-message-subject">
                <h3>{{selectedMessage.Subject}}</h3>
                <div class="neurotic-send-message-content">
                    <p class="neurotic-sender-link" ng-if="currentStatus.activeTab != 'sent'">
                        <a ng-href="{{selectedMessage.SenderAbsoluteUrl}}">
                            {{selectedMessage.Sender.UserName | isEmpty : messageDefaults.neuroticUserName:selectedMessage.Sender.UserName}}
                        </a> wrote at {{selectedMessage.Created}}
                    </p>
                    <p class="neurotic-sender-link" ng-if="currentStatus.activeTab == 'sent'">
                        <a ng-href="{{selectedMessage.SenderAbsoluteUrl}}">
                            {{selectedMessage.Sender.UserName}}
                        </a> wrote at {{selectedMessage.Created}}
                    </p>
                    <a ng-if="selectedMessage.IsReportAbuseDisplayed == true" ng-href="{{selectedMessage.AbuseReportAbsoluteUrl}}" class="abuse-button neurotic-abuse-btn">Report Abuse</a>
                    
                </div>
            </div>
        </div>
        <div class="body clearfix">
            <div ng-bind-html="selectedMessage.Body">

            </div>
        </div>
        <div class="message-reply" ng-hide="sendMessage.disableReplyBtn == false || sendMessage.sendResult.hasError == false">
            <textarea rows="2" cols="20" class="messages-reply-box text-box"
                      ng-model="sendMessage.replyContent"
                      ng-disabled="sendMessage.disableSendBtn == true"></textarea>
            <div class="password-warning">Remember, Neurotic staff will never ask you for your password. People who ask for your password are trying to steal your account.</div>
            <input type="checkbox" id="includePreviousMessage" ng-checked="sendMessage.includePreviousMessage" ng-model="sendMessage.includePreviousMessage">
            <label for="includePreviousMessage">Include Previous Message</label>
            <div class="neurotic-sendMessage-action">
                <button class="neurotic-sendMessage"
                        ng-click="sendReply()"
                        ng-disabled="sendMessage.disableSendBtn == true || sendMessage.replyContent.length == 0">
                    Send reply
                </button>
            </div>
        </div>
    </div>
</div>
@endverbatim
