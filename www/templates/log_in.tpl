{if $logged_out}
<div class="alert alert-warning">
    <p class="text-center">You've been automatically logged out because you've been inactive for over
        <?php print $SESSION_TIMEOUT; ?> minutes. Click on the 'Log in' link to get back into the system.</p>
</div>
{/if}

{render_header title="$ORGANISATION_NAME account manager - log in"}
<div class="container">
    <div class="col-sm-8">

        <div class="panel panel-default">
            <div class="panel-heading text-center">登入账户管理页面</div>
            <div class="panel-body text-center">

                {if $display_unauth}
                <div class="alert alert-warning">
                    Please log in to continue
                </div>
                {/if}

                {if $display_logged_out}
                <div class="alert alert-warning">
                    You were logged out because your session expired. Log in again to continue.
                </div>
                {/if}

                {if $invalid}
                <div class="alert alert-warning">
                    The username and/or password are unrecognised.
                </div>
                {/if}

                <form class="form-horizontal" action='' method='post'>
                    {if isset($redirect_to) and ($redirect_to != "")}<input type="hidden" name="redirect_to" value={$redirect_to}>{/if}

                    <div class="form-group">
                        <label for="username" class="col-sm-4 control-label">用户名</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="user_id" name="user_id">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="col-sm-4 control-label">密码</label>
                        <div class="col-sm-6">
                            <input type="password" class="form-control" id="confirm" name="password">
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-default">登录</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
{render_footer}
