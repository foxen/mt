<?php
/**
 * controllers/add_agent.php, 
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
$idLicense = isset($_POST["licenseIdField"]) ? $_POST["licenseIdField"]: 0;
$grpNames = isset($_POST["agentGrpsField"]) ? $_POST["agentGrpsField"]: 0;
$agentName = isset($_POST["agentNameField"]) ? $_POST["agentNameField"]: 0;



$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();


$objNetbooksAgentsModel = new Netbooks_Agents_Model($dbConfArr);
$objNetbooksLicensesModel = new Netbooks_Licenses_Model($dbConfArr);

if((($idAgent + $idLicense) > 0) && is_string($grpNames) && 
                                    is_string($agentName)){
    
    $objNetbooksAgentsModel->insert_agent($agentName, 
                                          $idAgent, 
                                          $idLicense, 
                                          $grpNames, 
                                          $isDebt, 
                                          $isQuantity, 
                                          $isPaused);
    
    $objNetbooksLicensesModel->set_taken($idLicense);
    
    $ret["success"] = true;
}else{
    $ret["errors"]["reason"] = "Ошибочные параметры";
}


$ret = json_encode($ret);

echo $ret;
?>
