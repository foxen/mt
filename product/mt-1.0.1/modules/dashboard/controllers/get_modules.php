<?php
/**
 * controllers/get_modules.php, 
 * mt-1.0.1,
 * dashboard
 * 
 * контроллер возвращающий активные
 * модули с конфигурацией элементов dashboard
 */

session_start();

if(isset($_SESSION['user_id'])){
    $userId = $_SESSION['user_id'];  
}
else{
    echo '';
    exit();
}


$inst = dirname(__FILE__);

require_once("../../../classes/init.class.php");

$objInit = new Init($inst);

$dbConfArr = $objInit->get_db_config();

$modulesArr = array();

if ($confDir = opendir($objInit->instance.'/configuration/')){
	while (false !== ($confFile = readdir($confDir))){
		if ($confFile != '.' && $confFile != '..' && $confFile != 'dashboard.ini' && $confFile != 'application.ini'){
			$fNameArr = split('.ini',$confFile);
			$modulesArr[] = $fNameArr[0];
		}
	}
}

$objDashUr = new Dashboard_Ur_Model($dbConfArr);

$allowedModules = $objDashUr->get_allowed_modules($userId);

if ($allowedModules == 'all'){
    return_objects($modulesArr, $objInit);
}
else{
    if(is_array($allowedModules)){
        $allowedArr = array();
        foreach ($allowedModules as $value){
            $allowedArr[] = $value['object'];
        }
        $crossArr = array_intersect($modulesArr, $allowedArr);
        return_objects($crossArr, $objInit);
    }
}

function return_objects($crossArr, $objInit){
    if(is_array($crossArr)){
        $widgetsArr = array();
        foreach ($crossArr as $value){
            $widget = array();
            $widget['xtype'] = 'btnwidget';
            $widget['buttonIcon'] = $objInit->root.
                                    '/modules/'.$value.
                                    '/resources/images/module.png';
            $widget['buttonLabel'] = $value;
            $widget['hndlr'] = $objInit->root."/modules/".$value."/index.php";
            $widgetsArr[] = $widget;
        }
        echo json_encode($widgetsArr);
        exit();
    }
}
echo 'none';
?>
