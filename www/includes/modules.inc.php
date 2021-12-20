<?php

 #Modules and how they can be accessed.

 #access:
 #auth = need to be logged-in to see it
 #hidden_on_login = only visible when not logged in
 #admin = need to be logged in as an admin to see it

 $MODULES = array(
                    'log_in'          => array('title' => '登录', 'display' => 'hidden_on_login'),
                    'change_password' => array('title' => '修改密码', 'display' => 'auth'),
                    'account_manager' => array('title' => '管理账户', 'display' => 'admin'),
                    'log_out'         => array('title' => '登出', 'display' => 'auth')
                  );

if ($ACCOUNT_REQUESTS_ENABLED == TRUE) {
  $MODULES['request_account'] = array('title' => '申请账户', 'display' => 'hidden_on_login');
}

?>
