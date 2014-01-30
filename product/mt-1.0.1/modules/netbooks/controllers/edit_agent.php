<?php
/**
 * controllers/edit_agent.php, 
 * mt-1.0.1,
 * netbooks
 * 
 * контроллер сохраняет лицензию
 * 
 */

session_start();

$isPaused = isset($_POST["ispaused"]) ? 1: 0;
$isDebt = isset($_POST["isdebt"]) ? 1: 0;
$isQuantity = isset($_POST["isquantity"]) ? 1: 0;
$idAgent = isset($_POST["agentIdField"]) ? $_POST["agentIdField"]: 0;

//$idAgent = 1;

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();


$objNetbooksAgentsModel = new Netbooks_Agents_Model($dbConfArr);


if($idAgent > 0){
    
    $objNetbooksAgentsModel->edit_agent($idAgent, $isDebt, 
                                          $isQuantity, $isPaused);
    $ret["success"] = true;
}else{
    $ret["errors"]["reason"] = "Ошибочные параметры";
}


$ret = json_encode($ret);

echo $ret;
?>
