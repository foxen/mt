<?php
/**
 * controllers/get_agents.php, 
 * mt-1.0.1,
 * netbooks
 * 
 * контроллер возвращающий список агентов
 * 
 */

session_start();

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objNetbooksLicensesModel = new Netbooks_Agents_Model($dbConfArr);

echo $objNetbooksLicensesModel->get_agents();

?>
