<?php
/**
 * index.php, mt-1.0.1
 * 
 * Точка входа приложения.
 * Читает конфигурацию, 
 * инициализирует базу данных,
 * оставляет запись в логе,
 * вызывает модуль по умолчанию
 */

session_start();

$inst = dirname(__FILE__);

require_once($inst."/classes/init.class.php");

$objInit = new Init($inst);

$dbConfArr = $objInit->get_db_config();

$objAppLogModel = new App_Log_Model($dbConfArr);

$loginConfigArr = $objInit->get_login_config();

$objLogin = new Login($loginConfigArr);

$objAppLogModel->add_event('application launched');

?>
