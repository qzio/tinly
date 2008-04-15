<?php 
define('BASE_PATH',dirname(__FILE__).'/');
require_once BASE_PATH.'lib/__autoload.php';
require_once BASE_PATH.'routes.php';
require_once BASE_PATH.'config.php';
session_start();
$Core = Core::singleton();
$Core->setInitialVars();
$Core->initiateController();
?>
