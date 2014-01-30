<?php
/**
 * controllers/save_order.php, 
 * mt-1.0.1,
 * client
 * 
 * контроллер сохраняет заказ
 * 
 */

session_start();

$orderJson = isset($_POST["orderstrings"]) ? $_POST["orderstrings"]: 0;


$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objClientAgentsModel = new Client_Agents_Model($dbConfArr);

if(is_array($orderArr = json_decode($orderJson))){
    echo $objClientAgentsModel->save_order($orderArr);    
}
?>
