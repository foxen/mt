<?php
/**
 * controllers/pause_agent.php, 
 * mt-1.0.1,
 * netbooks
 * 
 * контроллер приостанавливает агента
 * 
 */

session_start();


if(isset($_POST["agentId"]) && is_numeric($_POST["agentId"])){
    $idAgent = $_POST["agentId"];
}

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objNetbooksAgentsModel = new Netbooks_Agents_Model($dbConfArr);

if(isset($idAgent)){
    $objNetbooksAgentsModel->set_started($idAgent);
    $ret["success"] = true;
}else{
    $ret["errors"]["reason"] = "Невозможно";
}

$ret = json_encode($ret);

echo $ret;
?>
