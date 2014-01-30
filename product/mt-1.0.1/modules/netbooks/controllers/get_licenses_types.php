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

$dbConfArr = $objInit->get_db_config();

$objNetbooksAgentsModel = new Netbooks_Licenses_Model($dbConfArr);

//не забыть расскоментить!!
//$objInit->is_logged();

?>
