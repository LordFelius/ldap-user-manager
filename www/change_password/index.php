<?php

set_include_path( ".:" . __DIR__ . "/../includes/");

include_once "web_functions.inc.php";
include_once "ldap_functions.inc.php";

set_page_access("user");

if (isset($_POST['change_password'])) {

    if ((!is_numeric($_POST['pass_score']) or $_POST['pass_score'] < 3) and $ACCEPT_WEAK_PASSWORDS != TRUE) { $not_strong_enough = 1; }
    if (preg_match("/\"|'/",$_POST['password'])) { $invalid_chars = 1; }
    if ($_POST['password'] != $_POST['password_match']) { $mismatched = 1; }

    if (!isset($mismatched) and !isset($not_strong_enough) and !isset($invalid_chars) ) {

        $ldap_connection = open_ldap_connection();
        ldap_change_password($ldap_connection,$USER_ID,$_POST['password']) or die("change_ldap_password() failed.");
        $user_name = ldap_get_userName_from_userId($ldap_connection,    $USER_ID);
        $user_mail = ldap_get_user_attributes($ldap_connection,$user_name,array('mail'))[0]['mail'][0];

        render_header("$ORGANISATION_NAME account manager - password changed");
        ?>
        <div class="alert alert-success">
            <p class="text-center">密码修改成功！</p>
        </div>
        <?php
        $mail_subject = "您在 $ORGANISATION_NAME 的密码已被修改。";
        $mail_body = <<<EoT
您在 <a href="${SITE_PROTOCOL}${SERVER_HOSTNAME}${SERVER_PATH}">{$ORGANISATION_NAME}</a>的密码已被自助修改。
<p>
若并非您的操作，请联系站点管理员。<br>
EoT;
        include_once "mail_functions.inc.php";
        $sent_email = send_email($user_mail,$user_mail,$mail_subject,$mail_body);
        render_footer();
        exit(0);
    }

}

render_header("Change your $ORGANISATION_NAME password");

if (isset($not_strong_enough)) {  ?>
    <div class="alert alert-warning">
        <p class="text-center">The password wasn't strong enough.</p>
    </div>
<?php }

if (isset($invalid_chars)) {  ?>
    <div class="alert alert-warning">
        <p class="text-center">The password contained invalid characters.</p>
    </div>
<?php }

if (isset($mismatched)) {  ?>
    <div class="alert alert-warning">
        <p class="text-center">两次输入不匹配。</p>
    </div>
<?php }

?>

<script src="<?php print $SERVER_PATH; ?>js/zxcvbn.min.js"></script>
<script type="text/javascript" src="<?php print $SERVER_PATH; ?>js/zxcvbn-bootstrap-strength-meter.js"></script>
<script type="text/javascript">$(document).ready(function(){	$("#StrengthProgressBar").zxcvbnProgressBar({ passwordInput: "#password" });});</script>

<div class="container">
    <div class="col-sm-6">

        <div class="panel panel-default">
            <div class="panel-heading text-center">修改密码</div>

            <ul class="list-group">
                <li class="list-group-item">输入你的<?php print $ORGANISATION_NAME; ?>账户新密码，并在<b>确认密码</b>框中再次输入。
                输入框下方会显示你的密码强度。若两次输入不匹配，输入框将变为红色。</li>
            </ul>

            <div class="panel-body text-center">

                <form class="form-horizontal" action='' method='post'>

                    <input type='hidden' id="change_password" name="change_password">
                    <input type='hidden' id="pass_score" value="0" name="pass_score">

                    <div class="form-group" id="password_div">
                        <label for="password" class="col-sm-4 control-label">密码</label>
                        <div class="col-sm-6">
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                    </div>

                    <script>
                        function check_passwords_match() {

                            if (document.getElementById('password').value != document.getElementById('confirm').value ) {
                                document.getElementById('password_div').classList.add("has-error");
                                document.getElementById('confirm_div').classList.add("has-error");
                            }
                            else {
                                document.getElementById('password_div').classList.remove("has-error");
                                document.getElementById('confirm_div').classList.remove("has-error");
                            }
                        }
                    </script>

                    <div class="form-group" id="confirm_div">
                        <label for="password" class="col-sm-4 control-label">确认密码</label>
                        <div class="col-sm-6">
                            <input type="password" class="form-control" id="confirm" name="password_match" onkeyup="check_passwords_match()">
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-default">修改密码</button>
                    </div>

                </form>

                <div class="progress">
                    <div id="StrengthProgressBar" class="progress progress-bar"></div>
                </div>

            </div>
        </div>

    </div>
</div>
<?php

render_footer();

?>

