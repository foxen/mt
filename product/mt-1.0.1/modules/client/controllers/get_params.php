<?php
/**
 * controllers/get_params.php, 
 * mt-1.0.1,
 * client
 * 
 * контроллер возвращающий параметры
 * 
 */

session_start();

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objClientAgentsModel = new Client_Agents_Model($dbConfArr);

echo $objClientAgentsModel->get_params();

?>
