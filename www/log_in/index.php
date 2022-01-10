<?php

set_include_path( ".:" . __DIR__ . "/../includes/");

include "web_functions.inc.php";
include "ldap_functions.inc.php";

if (isset($_GET["unauthorised"])) { $display_unauth = TRUE; }
if (isset($_GET["session_timeout"])) { $display_logged_out = TRUE; }
if (isset($_GET["redirect_to"])) { $redirect_to = $_GET["redirect_to"]; }


if (isset($_POST["user_id"]) and isset($_POST["password"])) {

    $ldap_connection = open_ldap_connection();
    $user_auth = ldap_auth_username($ldap_connection,$_POST["user_id"],$_POST["password"]);
    $is_admin = ldap_is_group_member($ldap_connection,$LDAP['admins_group'],$_POST["user_id"]);

    ldap_close($ldap_connection);

    if ($user_auth != FALSE) {

        set_passkey_cookie($user_auth,$is_admin);
        if (isset($_POST["redirect_to"])) {
            header("Location: //${_SERVER['HTTP_HOST']}" . base64_decode($_POST['redirect_to']) . "\n\n");
        }
        else {

            if ($IS_ADMIN) { $default_module = "account_manager"; } else { $default_module = "change_password"; }
            header("Location: //${_SERVER['HTTP_HOST']}${SERVER_PATH}$default_module?logged_in\n\n");
        }
    }
    else {
        header("Location: //${_SERVER['HTTP_HOST']}${THIS_MODULE_PATH}/index.php?invalid\n\n");
    }

}
else {
#==============================================================================
# Smarty
#==============================================================================
    if (!defined("SMARTY")) {
        define("SMARTY", "../3rd/Smarty/Smarty.class.php");
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
    $smarty->assign('logged_out',isset($_GET['logged_out']));
    $smarty->assign('invalid',isset($_GET['invalid']));

    $smarty->assign('display_unauth',isset($display_unauth));
    $smarty->assign('display_logged_out',isset($display_logged_out));

    $smarty->assign('redirect_to',isset($redirect_to) ? $redirect_to : NULL);
//$smarty->assign('',);

    #functions
    $smarty->registerPlugin("function", "render_header", "render_header");
    $smarty->registerPlugin("function", "render_footer", "render_footer");

    $smarty->display('log_in.tpl');
    }
    ?>
