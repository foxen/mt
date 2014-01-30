<?php
/**
 * controllers/get_groups.php, 
 * mt-1.0.1,
 * client
 * 
 * контроллер возвращающий список групп агента
 * 
 */

session_start();

$idAgent = isset($_POST["idagent"]) ? $_POST["idagent"]: 0;

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objClientAgentsModel = new Client_Agents_Model($dbConfArr);

echo $objClientAgentsModel->get_groups($idAgent);

?>
