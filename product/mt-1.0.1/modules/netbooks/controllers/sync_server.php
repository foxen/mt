<?php
/**
 * controllers/sync_server.php, 
 * mt-1.0.1,
 * netbooks
 * 
 * контроллер откликается на запрос синхронизации
 * 
 */

//session_start();

$license = isset($_POST["n"]) ? $_POST["n"]: "";
$action  = isset($_POST["a"]) ? $_POST["a"]: "";
$orders  = isset($_POST["f"]) ? $_POST["f"]: "";
$chsum   = isset($_POST["m"]) ? $_POST["m"]: "";
$idfile  = isset($_POST["i"]) ? $_POST["i"]: "";

$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$dbConfArr = $objInit->get_db_config();

$objNetbooksLicensesModel = new Netbooks_Licenses_Model($dbConfArr);
$objNetbooksOrdersModel   = new Netbooks_Orders_Model($dbConfArr);
$result = 'none';
//$result = isset($_POST["a"]) ? $_POST["a"]: "sss";;

$rnd = time().rand(1,999);

$pth      = $objInit->instance.'/modules/'.$objInit->module.
            '/exchange/in/'.$rnd.'/';
$fileName = 'orders.json';
$fn       = $pth.$fileName;

switch($action){
    case 1:
        $result = 'none';
		$licArr = $objNetbooksLicensesModel->test_license($license);
        $idLicense = isset($licArr['id']) ? $licArr['id']: 0;
        $licPath = isset($licArr['export_path']) ? $licArr['export_path']: '';
        if ($licPath != ''){
            $licPath = $objInit->instance.'/modules/'.$objInit->module.'/public/'.$licPath.'/';
            if(!(is_dir($licPath))){
				mkdir($licPath);
			}
        }else{
            $licPath = $objInit->instance.'/modules/'.$objInit->module.'/public/';
        }
		$flag = false;
		if(($licArr!='invalid') &&
			($idLicense>0) &&
			is_array($_FILES["f"]) &&
			($chsum != "")) {
			
			if(!(is_dir($pth))){
				mkdir($pth,0777);
			}
			$f = move_uploaded_file($_FILES['f']['tmp_name'], $fn.'.zip');
			if($f && (md5_file($fn.'.zip') == $chsum)){
				$zip = new ZipArchive;
				$res = $zip->open($fn.'.zip');
				if(is_file($fn)){
					unlink($fn);
				}
				if($res === true){
					$zip->extractTo($pth);	
				}
				$zip->close();
				unlink($fn.'.zip');
				if(is_file($fn)){
					$contentArr = json_decode(file_get_contents($fn), true);
                    $objNetbooksOrdersModel->load_orders($contentArr,$rnd,$idLicense);
					unlink($fn);
					rmdir($pth);
                    $objNetbooksOrdersModel->export_amt($rnd, $licPath);
                    $result = 'done';
				}
            }
		}
        break;
    case 2:
        $licArr = $objNetbooksLicensesModel->test_license($license);
        $idLicense = isset($licArr['id']) ? $licArr['id']: 0;
        if($idLicense>0){
            $msDbConfArr = $objInit->modConfigArr['mssql'];
            $objNetbooksAgentsMsModel = new Netbooks_Agents_Ms_Model($dbConfArr, $msDbConfArr);
        
            $p = $objInit->instance.'/modules/'.$objInit->module.
                                                        '/exchange/out';
        
            $f = $objNetbooksAgentsMsModel->sync_agents($idLicense, $p);
        
            $fn = $f.".json.zip";
        
            $fullFn = $p.'/'.$fn;
            header("size:".filesize($fullFn)); 
            header('Content-type: application/zip');
            header('Content-Disposition: attachment; filename="'.
                                                            $fn.'"');
            readfile($fullFn);
        }   
        break;
    case 3:
        $fileName = $objInit->instance.'/modules/'.$objInit->module.
                    '/exchange/out/'.$idfile.'.json';
        if(is_file($fileName)){
            $ch = md5_file($fileName);
            unlink($fileName);
            unlink($fileName.'.zip');
            
            $result = $ch;
        }
        
        
        break;
}

echo $result;
//$objNetbooksAgentsModel = new Netbooks_Agents_Model($dbConfArr);


?>
