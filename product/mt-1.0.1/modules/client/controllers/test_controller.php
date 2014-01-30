<?php
/**
 * controllers/save_order.php, 
 * mt-1.0.1,
 * client
 * 
 * контроллер сохраняет заказ
 * 
 */

session_start();

//$params = isset($_POST["paramets"]) ? $_POST["paramets"]: 0;

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);


$dbConfArr = $objInit->get_db_config();

$objClientAgentsModel = new Client_Agents_Model($dbConfArr);

$a = $objClientAgentsModel->get_orders_tosend();
print_r($a);

$content = json_encode($a);
            
            $pth = $objInit->instance.'/modules/'.$objInit->module.'/exchange/out/';
            $fileName = 'orders.json';
            $fn = $pth.$fileName;
            $f = fopen($fn,"w");
            fclose($f);
            file_put_contents($fn,$content);
            
            if(file_exists($fn.'zip')){
                unlink($fn.'.zip');
            }
            
            $zip = new ZipArchive();
            $zip->open($fn.'.zip', ZIPARCHIVE::CREATE);
            $zip->addFile($fn, $fileName);
            $zip->close();
        
            unlink($fn);

?>
