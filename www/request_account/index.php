<?php

set_include_path( ".:" . __DIR__ . "/../includes/");
session_start();

include_once "web_functions.inc.php";
include_once "ldap_functions.inc.php";

render_header("$ORGANISATION_NAME - request an account");

if ($ACCOUNT_REQUESTS_ENABLED == FALSE) {

    ?><div class='alert alert-warning'><p class='text-center'>Account requesting is disabled.</p></div><?php

    render_footer();
    exit(0);

}

if($_POST) {

    $error_messages = array();

    if (!isset($_POST['validate']) or strcasecmp($_POST['validate'], $_SESSION['proof_of_humanity']) != 0) {
        array_push($error_messages, "验证码错误。");
    }

    $attribute_map = ldap_complete_account_attribute_array();
    $request_index = ldap_complete_request_index();
    foreach ($request_index as $attribute) {
        $attr_r = $attribute_map[$attribute];
        $label = $attr_r['label'];
        if (!isset($_POST[$attribute]) or $_POST[$attribute] == "") {
            array_push($error_messages, "未输入 $label.");
        } else {
            $$attribute = filter_var($_POST[$attribute], FILTER_SANITIZE_STRING);
        }
    }

    if (isset($_POST['notes']) and $_POST['notes'] != "") {
        $notes = filter_var($_POST['notes'], FILTER_SANITIZE_STRING);
    }


    if (count($error_messages) > 0) { ?>
        <div class="alert alert-danger" role="alert">
            表单填写不合要求：
            <p>
            <ul>
                <?php
                foreach($error_messages as $message) {
                    print "<li>$message</li>\n";
                }
                ?>
            </ul>
        </div>
        <?php
    }
    else {
        $mail_subject = "someone has requested an account for $ORGANISATION_NAME.";
        $link_url="${SITE_PROTOCOL}${SERVER_HOSTNAME}${SERVER_PATH}account_manager/new_user.php?account_request";
        foreach ($request_index as $attribute) {
            $attr_r = $attribute_map[$attribute];
            $label = $attr_r['label'];
            $value = $$attribute;
            $fragment_url = "&$attribute=$value";
            $link_url = $link_url . $fragment_url;
        }
        if (!isset($email)) { $email = "n/a"; }
        if (!isset($notes)) { $notes = "n/a"; }

        $mail_body = <<<EoT
A request for an $ORGANISATION_NAME account has been sent:
<p>
Email: <b>$email</b><br>
Notes: <pre>$notes</pre><br>
<p>
<a href="$link_url">Create this account.</a>
EoT;

        include_once "mail_functions.inc.php";
        $sent_email = send_email($ACCOUNT_REQUESTS_EMAIL,"$ORGANISATION_NAME account requests",$mail_subject,$mail_body);
        if ($sent_email) {
            $sent_email_message = "  账户申请已发送到后台管理。管理员将在数日内进行处理。";
        }
        else {
            $sent_email_message = "  邮件未成功发送。请联系管理员。";
        }
        ?>
        <div class="container">
            <div class="col-sm-8">
                <div class="panel panel-default">
                    <div class="panel-body"><?php print $sent_email_message; ?></div>
                </div>
            </div>
        </div>
        <?php

        render_footer();
        exit(0);

    }
}
?>
<div class="container">
    <div class="col-sm-8">

        <div class="panel panel-default">
            <div class="panel-body">
                填写下方表格以申请建立<?php print $ORGANISATION_NAME; ?>账户。
                管理员会在数日内向您的邮箱发送通知邮件。
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading text-center">申请建立<?php print $ORGANISATION_NAME; ?>账户</div>
            <div class="panel-body text-center">

                <form class="form-horizontal" action='' method='post'>
                    <?php
                    $attribute_map = ldap_complete_account_attribute_array();
                    $request_index = ldap_complete_request_index();
                    foreach ($request_index as $attribute) {
                        $attr_r = $attribute_map[$attribute];
                        $label = $attr_r['label'];
                        if ($attribute == $LDAP['search_attribute']) { $label = "<strong>$label</strong><sup>&ast;</sup>"; }
                        ?>
                        <div class="form-group">
                            <label for="<?php print $attribute; ?>" class="col-sm-4 control-label"><?php print $label; ?></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="<?php print $attribute; ?>" name="<?php print $attribute; ?>"
                                       placeholder="必填" <?php if (isset($$attribute)) {$default_value=$$attribute; print "value='$default_value'"; } ?>>
                            </div>
                        </div>
                        <?php
                    }
                    ?>

                    <div class="form-group">
                        <label for="Notes" class="col-sm-4 control-label">备注</label>
                        <div class="col-sm-6">
                            <textarea class="form-control" id="notes" name="notes" placeholder=""><?php if (isset($notes)) { print $notes; } ?></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="validate" class="col-sm-4 control-label">验证码</label>
                        <div class="col-sm-6">
      <span class="center-block">
        <img src="human.php" class="human-check" alt="Non-human detection">
        <button type="button" class="btn btn-default btn-sm" onclick="document.querySelector('.human-check').src = 'human.php?' + Date.now()">
         <span class="glyphicon glyphicon-refresh"></span> 刷新验证码
        </button>
      </span>
                            <input type="text" class="form-control center-block" id="validate" name="validate" placeholder="输入图片中的验证码">
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-default">发送申请</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <?php
    render_footer();
    ?>
