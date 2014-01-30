<?php
/**
 * login.class.php, mt-1.0.1
 * 
 * Класс определяет утилиты для проверки логина и пароля,
 * а так же результаты этой проверки в виде редиректа
 * на нужные страницы
 * 
 */

class Login{
	
	var $isLoginReq;
	var $appName;
	var $defModule;
	var $appState;
	var $instance;
    
	function __construct($loginConfig){
		$this->isLoginReq = $loginConfig['isLoginReq'];
		$this->appName    = $loginConfig['appName'];
		$this->defModule  = $loginConfig['defModule'];
		$this->appState   = $loginConfig['appState'];
		$this->instance   = $loginConfig['instance'];
		
		if (!isset($_SESSION['user_id']) && ($this->isLoginReq == 1)){
			$this->login_redirect();
		}
        else{
            $this->module_redirect();
        }
	}
    
    function login_redirect(){
		$objExtjs = new Extjs();
        $content = file_get_contents($this->instance.'/views/login.js');
        $root = '/'.$this->appName;
        if ($this->appState == 'dev' || $this->appState == 'testing'){
            $root = $root.'/'.$this->appState;
        }
        $objExtjs -> show_page($root, $content,"Аутентификация");
        
	}
	
	function module_redirect(){
        $root = '/'.$this->appName;
        if ($this->appState == 'dev' || $this->appState == 'testing'){
            $root = $root.'/'.$this->appState;
        }
        $lnk = $root."/modules/".$this->defModule."/index.php";
        header("Location: ".$lnk); 
        
        exit();
        
	}
}


