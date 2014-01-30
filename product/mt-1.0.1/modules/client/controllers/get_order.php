<?php
/**
 * controllers/get_agents.php, 
 * mt-1.0.1,
 * client
 * 
 * контроллер возвращающий список агентов
 * 
 */

session_start();

$idA = isset($_POST["ida"]) ? $_POST["ida"]: 0;
$idC = isset($_POST["idc"]) ? $_POST["idc"]: 0;
$idO = isset($_POST["ido"]) ? $_POST["ido"]: 0;


$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objNetbooksLicensesModel = new Client_Agents_Model($dbConfArr);

if ($idO == 0){
    if (($idA + $idC)>0){
        echo $objNetbooksLicensesModel->get_new_order($idA,$idC);
        exit;
    }
}

if($idO > 0){
    echo $objNetbooksLicensesModel->get_order($idO);
    exit;
}


?>
