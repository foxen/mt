<?php
/**
 * controllers/add_license.php, 
 * mt-1.0.1,
 * netbooks
 * 
 * контроллер сохраняет лицензию
 * 
 */

session_start();

$isPaused = isset($_POST["ispaused"]) ? 1: 0;

$name = isset($_POST["licensename"]) ? $_POST["licensename"]: "";

$format = isset($_POST["idformat"]) ? $_POST["idformat"]: 1;

$exportPath = isset($_POST["licensepath"]) ? $_POST["licensepath"]: "";

if(isset($_POST["idlicense"]) && is_numeric($_POST["idlicense"])){
    $idLicense = $_POST["idlicense"];
}

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objNetbooksAgentsModel = new Netbooks_Licenses_Model($dbConfArr);

$valid = false;

if (is_string($name)){
    if(strlen($name)<51){
        $valid = true;
    }
} 

if (!$valid){
    $ret["errors"]["reason"] = "Некорректное имя лицензи";
    $ret = json_encode($ret);
    echo $ret;
    exit();
}


$flag = $objNetbooksAgentsModel->get_license_id($name);

if ($flag[0]['result'] === 'none' || isset($idLicense)){
    if(isset($idLicense)){
        $objNetbooksAgentsModel->update_license($idLicense, $name, $format, $isPaused,$exportPath);
    }else{
        $objNetbooksAgentsModel->insert_license($name, $format, $isPaused,$exportPath);
    }
    $ret["success"] = true;
}else{
	$ret["errors"]["reason"] = "Лицензия с таким именем уже существует";
}

$ret = json_encode($ret);

echo $ret;
?>
