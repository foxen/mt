<?php
/**
 * controllers/remove_agent.php, 
 * mt-1.0.1,
 * netbooks
 * 
 * контроллер удаляет агента
 * 
 */

session_start();

if(isset($_POST["idAgent"]) && is_numeric($_POST["idAgent"])){
    $idAgent = $_POST["idAgent"];
}

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objNetbooksAgentsModel = new Netbooks_Agents_Model($dbConfArr);

if(isset($idAgent)){
    
    $objNetbooksAgentsModel->set_deleted($idAgent);
    $ret["success"] = true;
}else{
    $ret["errors"]["reason"] = "Ошибочные параметры";
}

$ret = json_encode($ret);

echo $ret;
?>
