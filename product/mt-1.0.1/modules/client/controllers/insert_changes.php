<?php
/**
 * controllers/insert_changes.php, 
 * mt-1.0.1,
 * client
 * 
 * контроллер возвращающий список агентов
 * 
 */

session_start();

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

//$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objClientAgentsModel = new Client_Agents_Model($dbConfArr);

$fName = '13230632997783.json.zip';
$fJsonName = str_replace('.zip','',$fName);
$inPath = $objInit->instance.'/modules/'.$objInit->module.'/exchange/in/';
$file = $inPath.$fName;

if(is_file($file)){
    $zip = new ZipArchive;
    if ($zip->open($file) === TRUE) {
        $zip->extractTo($inPath);
        $zip->close();
        //unlink($file);
    }
}
$content = file_get_contents($inPath.$fJsonName);
$contentArr = json_decode($content,true);
if (is_array($contentArr)){
    $objClientAgentsModel->insert_updates($contentArr);
}

//print_r($objClientAgentsModel->get_price_list(10108,6938));
//echo $objClientAgentsModel->get_clients();

?>
