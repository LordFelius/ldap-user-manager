{render_header title="$ORGANISATION_NAME account manager - log in"}
<div class="container">
    <div class="col-sm-8">


        <div class="panel panel-default">
            <div class="panel-heading text-center">使用邮箱重置密码</div>
            <div class="panel-body text-center">

                {if !$sent_mail}
                {if $invalid}
                <div class="alert alert-warning">
                    没有找到该邮箱对应的账户。
                </div>
                {/if}

                <div class="panel panel-default">
                    <div class="panel-body">
                        输入您的注册邮箱以重置<?php print $ORGANISATION_NAME; ?>账户的密码。
                        新的密码将在稍后发送到您的注册邮箱。
                    </div>
                </div>

                <form class="form-horizontal" action='' method='post'>
                    {if isset($redirect_to) and ($redirect_to != "")}<input type="hidden" name="redirect_to" value={$redirect_to}>{/if}

                    <div class="form-group">
                        <label for="email" class="col-sm-4 control-label">注册邮箱</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="email" name="email">
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-default">发送重置申请</button>
                    </div>
                </form>
                {/if}

                {if $sent_mail}
                <div class="panel panel-default">
                    <div class="panel-body">
                        {$sent_mail_message}
                    </div>
                </div>
                {/if}
            </div>
        </div>
    </div>
{render_footer}
