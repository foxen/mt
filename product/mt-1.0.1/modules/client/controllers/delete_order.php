<?php
/**
 * controllers/delete_order.php, 
 * mt-1.0.1,
 * client
 * 
 * контроллер удаляющий заказ
 * 
 */

session_start();

$idO = isset($_POST["ido"]) ? $_POST["ido"]: 0;


$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objClientAgentsModel = new Client_Agents_Model($dbConfArr);

if($idO > 0){
    echo $objClientAgentsModel->delete_order($idO);
}


?>
