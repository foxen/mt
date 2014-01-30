<?php
/**
 * logout.php, mt-1.0.1
 * 
 * контроллер завершающий сессию
 */
session_start();
unset($_SESSION['user_id']);
$inst = dirname(__FILE__);
require_once("../classes/init.class.php");
$objInit = new Init($inst);
$objInit->is_logged();

?>
