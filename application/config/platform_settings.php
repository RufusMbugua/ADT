<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

$config['user_table'] = 'users';
$config['access_level_table'] = 'access_level';
$config['user_log_table'] = 'userlog';

$config['username_column'] = "username";
$config['password_column'] = "Password";
$config['access_level_column'] = "access_level";
$config['active_column'] = "Active";
$config['authentication_column'] = "Signature";
$config['time_updated_column'] = "Time_Created";

$config['access_level_indicator'] = "indicator";
$config['admin_indicator'] = "admin";
$config['temp_indicator'] = "temp";

$config['attempt_limit'] = 4;
$config['normal_expiry'] = 30;
$config['temp_expiry'] = 14;

$config['module_after_login'] = "home";
