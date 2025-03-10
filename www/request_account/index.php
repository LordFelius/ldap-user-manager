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
        array_push($error_messages, "The validation text didn't match the image.");
    }

    $attribute_map = ldap_complete_account_attribute_array();
    $request_index = ldap_complete_request_index();
    foreach ($request_index as $attribute) {
        $attr_r = $attribute_map[$attribute];
        $label = $attr_r['label'];
        if (!isset($_POST[$attribute]) or $_POST[$attribute] == "") {
            array_push($error_messages, "You didn't enter  $label.");
        } else {
            $$attribute = filter_var($_POST[$attribute], FILTER_SANITIZE_STRING);
        }
    }

    if (isset($_POST['notes']) and $_POST['notes'] != "") {
        $notes = filter_var($_POST['notes'], FILTER_SANITIZE_STRING);
    }


    if (count($error_messages) > 0) { ?>
        <div class="alert alert-danger" role="alert">
            The request couldn't be sent because:
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
            $sent_email_message = "  Thank you. The request was sent and the administrator will process it as soon as possible.";
        }
        else {
            $sent_email_message = "  Unfortunately the request wasn't sent because of a technical problem.";
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
                Use this form to send a request for an account to an administrator at <?php print $ORGANISATION_NAME; ?>.
                If the administrator approves your request they'll get in touch with you to give you your new credentials.
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading text-center">Request an account for <?php print $ORGANISATION_NAME; ?></div>
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
                                       placeholder="Required" <?php if (isset($$attribute)) {$default_value=$$attribute; print "value='$default_value'"; } ?>>
                            </div>
                        </div>
                        <?php
                    }
                    ?>

                    <div class="form-group">
                        <label for="Notes" class="col-sm-4 control-label">Notes</label>
                        <div class="col-sm-6">
                            <textarea class="form-control" id="notes" name="notes" placeholder="Enter any extra information you think the administrator might need to know."><?php if (isset($notes)) { print $notes; } ?></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="validate" class="col-sm-4 control-label">Validation</label>
                        <div class="col-sm-6">
      <span class="center-block">
        <img src="human.php" class="human-check" alt="Non-human detection">
        <button type="button" class="btn btn-default btn-sm" onclick="document.querySelector('.human-check').src = 'human.php?' + Date.now()">
         <span class="glyphicon glyphicon-refresh"></span> Refresh
        </button>
      </span>
                            <input type="text" class="form-control center-block" id="validate" name="validate" placeholder="Enter the characters from the image">
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-default">Send request</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <?php
    render_footer();
    ?>
