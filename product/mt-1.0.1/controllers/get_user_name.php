<?php
/**
 * get_user_name.php, mt-1.0.1
 * 
 * контроллер возвращающий имя пользователя
 */
session_start();

$inst = dirname(__FILE__);
require_once("../classes/init.class.php");
$objInit = new Init($inst);
$dbConfArr = $objInit->get_db_config();
$objAppUsersModel = new App_Users_Model($dbConfArr);

$userName = $objAppUsersModel->get_user_name($_SESSION['user_id']);
//$userName = $objAppUsersModel->get_user_name(1);
echo $userName;

?>
