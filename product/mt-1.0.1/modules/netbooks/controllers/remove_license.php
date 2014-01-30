<?php
/**
 * controllers/remove_license.php, 
 * mt-1.0.1,
 * netbooks
 * 
 * контроллер приостанавливает лицензию
 * 
 */

session_start();


if(isset($_POST["idlicense"]) && is_numeric($_POST["idlicense"])){
    $idLicense = $_POST["idlicense"];
}

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objNetbooksLicensesModel = new Netbooks_Licenses_Model($dbConfArr);

if(isset($idLicense)){
    $objNetbooksLicensesModel->set_deleted($idLicense);
    $ret["success"] = true;
}else{
    $ret["errors"]["reason"] = "Невозможно";
}

$ret = json_encode($ret);

echo $ret;
?>
