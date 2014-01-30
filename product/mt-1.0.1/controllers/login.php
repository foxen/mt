<?php
/**
 * login.php, mt-1.0.1
 * 
 * контроллер проверяющий имя пользователя и пароль
 */
session_start();

$login = isset($_POST["loginUsername"]) ? $_POST["loginUsername"] : "";
$password = isset($_POST["loginPassword"]) ? $_POST["loginPassword"] : "";

$inst = dirname(__FILE__);
require_once("../classes/init.class.php");
$objInit = new Init($inst);
$dbConfArr = $objInit->get_db_config();
$objAppUsersModel = new App_Users_Model($dbConfArr);
$userId = $objAppUsersModel->test_user($login, $password);

$objAppLogModel = new App_Log_Model($dbConfArr);

if($userId['res']){
    $ret['success'] = true;
    $_SESSION['user_id'] = $userId['id'];
    $objAppLogModel->add_event('logon successfully',$userId['id']);
}
else{
    $ret['success'] = false;		
    $ret['errors']['reason'] = 'Неверное имя пользователя или пароль';
    $objAppLogModel->add_event('logon failture',$login.' '.$password);
}

echo json_encode($ret);

?>
