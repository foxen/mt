<?php
/**
 * controllers/get_licenses.php, 
 * mt-1.0.1,
 * netbooks
 * 
 * контроллер возвращающий список лицензий
 * 
 */

session_start();

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objNetbooksLicensesModel = new Netbooks_Licenses_Model($dbConfArr);

echo $objNetbooksLicensesModel->get_licenses_agents();

?>
