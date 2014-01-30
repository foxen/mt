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

$params = isset($_POST["paramets"]) ? $_POST["paramets"]: 0;

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objClientAgentsModel = new Client_Agents_Model($dbConfArr);

if(is_array($paramsArr = json_decode($params))){
    echo $objClientAgentsModel->set_params($paramsArr);    
}
?>
