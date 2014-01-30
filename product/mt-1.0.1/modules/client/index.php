<?php
/**
 * modules/netbooks/index.php, mt-1.0.1
 * 
 * Точка входа модуля netbooks
 * 
 */



session_start();

$inst = dirname(__FILE__);

require_once("../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objAppLogModel = new App_Log_Model($dbConfArr);

$objAppLogModel->add_event('module launched', 'netbooks');

$objExtjs = new Extjs();
$objExtjs->show_with_content('client', $_SESSION['user_id'], $objInit->instance, $objInit->root, false);


?>


