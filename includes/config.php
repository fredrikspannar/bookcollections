<?php

// simple security - deny access if no defined constant
if ( !defined("APP") ) die;

// supress php warnings and notices
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

/**
* Configuration settings
*/

global $settings; // define in global scope
$settings = array();

// mysql settings
$settings['db_name'] = '';
$settings['db_host'] = '';  
$settings['db_user'] = '';
$settings['db_password'] = '';
