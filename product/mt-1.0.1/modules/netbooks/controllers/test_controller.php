<?php
/**
 * controllers/save_order.php, 
 * mt-1.0.1,
 * netbooks
 * 
 * тестовый контроллер
 * 
 */

session_start();

//$params = isset($_POST["paramets"]) ? $_POST["paramets"]: 0;

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);


$dbConfArr = $objInit->get_db_config();

$objNetbooksOrdersModel = new Netbooks_Orders_Model($dbConfArr);
$ptn = $objInit->instance.'/modules/'.$objInit->module.
            '/exchange/in/1323463023616/';
rmdir($ptn);


?>
