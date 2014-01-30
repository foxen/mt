<?php
/**
 * controllers/get_agents_ms.php, 
 * mt-1.0.1,
 * netbooks
 * 
 * контроллер возвращающий список торговых представителей
 * с сервера ms sql
 */

session_start();

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

//$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();
$msDbConfArr = $objInit->modConfigArr['mssql'];

$objNetbooksAgentsMsModel = new Netbooks_Agents_Ms_Model($dbConfArr, $msDbConfArr);
echo $objNetbooksAgentsMsModel->get_agents();

//echo $objNetbooksAgentsMsModel->sync_agents(4,'/home/foxen');

?>
