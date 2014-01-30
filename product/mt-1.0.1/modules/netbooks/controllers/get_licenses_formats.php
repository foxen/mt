<?php
/**
 * controllers/get_licenses_formats.php, 
 * mt-1.0.1,
 * netbooks
 * 
 * контроллер возвращающий список форматов выгрзки
 * 
 */

session_start();

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objNetbooksAgentsModel = new Netbooks_Licenses_Model($dbConfArr);

echo $objNetbooksAgentsModel->get_formats();

?>
