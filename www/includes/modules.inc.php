<?php

 #Modules and how they can be accessed.

 #access:
 #auth = need to be logged-in to see it
 #hidden_on_login = only visible when not logged in
 #admin = need to be logged in as an admin to see it

 $MODULES = array(
                    'log_in'          => array('title' => 'log_in', 'display' => 'hidden_on_login'),
                    'change_password' => array('title' => 'change_password', 'display' => 'auth'),
                    'account_manager' => array('title' => 'account_manager', 'display' => 'admin'),
                    'log_out'         => array('title' => 'log_out', 'display' => 'auth')
                  );

if ($ACCOUNT_REQUESTS_ENABLED == TRUE) {
  $MODULES['request_account'] = array('title' => 'request_account', 'display' => 'hidden_on_login');
}

?>
