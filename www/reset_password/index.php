<?php

set_include_path( ".:" . __DIR__ . "/../includes/");

include "web_functions.inc.php";
include "ldap_functions.inc.php";

if (isset($_GET["unauthorised"])) { $display_unauth = TRUE; }
if (isset($_GET["session_timeout"])) { $display_logged_out = TRUE; }
if (isset($_GET["redirect_to"])) { $redirect_to = $_GET["redirect_to"]; }


if (isset($_POST["email"])) {

    $ldap_connection = open_ldap_connection();

    $user_valid = ldap_get_user_attributes($ldap_connection, $_POST["email"],array($LDAP['account_attribute']));

    ldap_close($ldap_connection);

    if (isset($user_valid[0])) {

        $id = $user_valid[0][$LDAP['account_attribute']][0];
        $mail_subject = "reset password request for $ORGANISATION_NAME.";
        $link_url="${SITE_PROTOCOL}${SERVER_HOSTNAME}${SERVER_PATH}account_manager/show_user.php?account_identifier=" . urlencode($id);

        $mail_body = <<<EoT
Someone has requested to reset $ORGANISATION_NAME account password:
<p>
Email: <b>$email</b><br>
<p>
<a href="$link_url">Approve the request</a>
EoT;

        include_once "mail_functions.inc.php";
        $sent_email = send_email($ACCOUNT_REQUESTS_EMAIL,"reset password request for $ORGANISATION_NAME",$mail_subject,$mail_body);
        if ($sent_email) {
            $sent_email_message = "重置密码申请已发送到后台管理。新的密码将在稍后发送到您的注册邮箱。";
        }
        else {
            $sent_email_message = "重置密码申请无法发送到后台管理。请联系管理员。";
        }
        }

    else {
        header("Location: //${_SERVER['HTTP_HOST']}${THIS_MODULE_PATH}/index.php?invalid\n\n");
    }
}

#==============================================================================
# Smarty
#==============================================================================
    if (!defined("SMARTY")) {
        define("SMARTY", "/usr/share/php/Smarty/Smarty.class.php");
    }
    require_once(SMARTY);

    $compile_dir = isset($smarty_compile_dir) ? $smarty_compile_dir : "../templates_c/";
    $cache_dir = isset($smarty_cache_dir) ? $smarty_cache_dir : "../cache/";

    $smarty = new Smarty();
    $smarty->escape_html = true;
    $smarty->setTemplateDir('../templates/');
    $smarty->setCompileDir($compile_dir);
    $smarty->setCacheDir($cache_dir);
    $smarty->debugging = isset($debug) ? $debug : FALSE;

    error_reporting(0);
    if ($debug) {
        error_reporting(E_ALL);
        # Set debug for LDAP
        ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    }

# Assign configuration variables
    $smarty->assign('invalid', isset($_GET['invalid']));
    $smarty->assign('sent_mail',isset($sent_email) && $sent_email);
    $smarty->assign('sent_mail_message',isset($sent_email_message) ? $sent_email_message : NULL);

//$smarty->assign('',);

    #functions
    $smarty->registerPlugin("function", "render_header", "render_header");
    $smarty->registerPlugin("function", "render_footer", "render_footer");

    $smarty->display('reset_password.tpl');
?>
