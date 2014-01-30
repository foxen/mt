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

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

//$objInit->is_logged();

$dbConfArr = $objInit->get_db_config();

$objClientAgentsModel = new Client_Agents_Model($dbConfArr);

$stage = isset($_GET["stage"]) ? $_GET["stage"]: 0;

$testServer = "ya.ru";

$pth      = $objInit->instance.'/modules/'.$objInit->module.
            '/exchange/out/';
$fileName = 'orders.json';
$fn       = $pth.$fileName;

$paramsArr = $objClientAgentsModel->get_params_sync();
$address   = $paramsArr['address']; 
$port      = $paramsArr['port'];
$license   = $paramsArr['license'];

$url = $objInit->modConfigArr['server']['path'];

//$uri = "http://".$address.':'.$port."/mt/dev/modules/netbooks/controllers/sync_server.php";
$uri = "http://".$address.':'.$port.$url;
$result["success"] = false;
$result["reason"]  = "no action";
//$stage = 'sendorders';
//$stage = 'getchanges';
switch ($stage){
    
    case "testconnection":
        $testconn = "ping -c 2 ".$testServer;
        exec($testconn, $res); 
        if( !(is_array($res)) || 
            !(isset($res[1])) || substr($res[1],0,2) != "64"){
            
            $result["success"] = false;
            $result["reason"]  = "Невозможно соедениться с интернет!";
        
        }
        else{
            $result["success"]=true;
            $result["reason"]="Соединение с интернет прошло успешно";
        }
        break;
    
    case "testorders":
        $isNewOrders       = $objClientAgentsModel->test_orders();
        $result["success"] = false;
        $result["reason"]  = "Ошибка локальной базы, обратитесь к разработчику!";
        if ($isNewOrders == 'presist' || $isNewOrders == 'none'){
            $result["success"] = true;
            $result["orders"]  = $isNewOrders;
            $result["reason"]  = "Нет неотправленных заказов";
            if ($isNewOrders == 'presist'){
                $result["reason"] = "Есть неотправленные заказы";
            }
        }
        break;
     
    case "createfile":
        $result["success"] = false;
        $result["reason"]  = "Невозможно сохранить файл с новыми заказами, обратитесь к разработчику";
        $arr               = $objClientAgentsModel->get_orders_tosend();
        if (is_array($arr)){
            $content = json_encode($arr);
            $f       = fopen($fn,"w");
            fclose($f);
            file_put_contents($fn,$content);
            if(file_exists($fn.'zip')){
                unlink($fn.'.zip');
            }
            $zip = new ZipArchive();
            $zip->open   ($fn.'.zip', ZIPARCHIVE::CREATE);
            $zip->addFile($fn, $fileName);
            $zip->close  ();
            unlink($fn);
            if(file_exists($fn.'.zip')){
                $result["success"] = true;
                $result["reason"]  = "Заказы успешно подготовленны";
            }
        }
        break;
    
    case "sendorders":
        $result["success"]=false;
        $result["reason"]="Невозможно отправить файл с новыми заказами!";
        if(file_exists($fn.'.zip')){
            $chsum = md5_file($fn.'.zip');
            $postVars=array("n" => $license, 
                            "a" => "1", 
                            "m" => $chsum, 
                            "f" => "@".$fn.'.zip');               
                $ch = curl_init($uri);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS,$postVars);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $response = curl_exec($ch);
                curl_close($ch);
                if ($response && ($response == 'done')){
                    $objClientAgentsModel->set_sended();
                    $result["success"]=true;
                    $result["reason"]="Заказы успешно отправленны";
                }
            }
        break;
    
    case "getchanges":
        $result["success"]=false;
        $result["reason"]="Невозможно получить обновления!";

        $f = $objInit->instance.'/modules/'.
                $objInit->module.'/exchange/in/in.json.zip';
            
        $fn = fopen($f, "w");
            
        $postVars = "n=".$license."&a=2";
            
        $ch = curl_init($uri);
            
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postVars);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FILETIME, 1);
            
        curl_setopt($ch, CURLOPT_FILE, $fn);
            
        curl_exec($ch);
  
        curl_close($ch);
        fclose($fn);
        
        $zip = new ZipArchive;
        $f;
        
        $res = $zip->open($f);
        if ($res === true) {
            $fid = str_replace('.json','', $zip->getNameIndex(0));
            $zip->extractTo($objInit->instance.'/modules/'.
                                    $objInit->module.'/exchange/in');
            $zip->close();
            unlink($f);
            
            $fjson = $objInit->instance.'/modules/'.
                    $objInit->module.'/exchange/in/'.$fid.'.json';
            
            $chsum = md5_file($fjson);
                
            $postVars = "&a=3"."&i=".$fid;
            $ch = curl_init($uri);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$postVars);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            if($chsum == $response){
                $content = file_get_contents($fjson);
                $contentArr = json_decode($content,true);
                if (is_array($contentArr)){
                    $objClientAgentsModel->insert_updates($contentArr);
                    //unlink($fjson);
                    $result["success"]=true;
                    $result["reason"]="Обновления успешно получены";
                }
            }  
        }
 
        break;
    
    default:
        $result["success"]=false;
        $result["reason"]="no valid action";
        break;
}

echo json_encode($result);

?>
