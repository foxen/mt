<?php
/**
 * init.class.php, mt-1.0.1
 * 
 * Класс принимает путь, читает конфигурацию,
 * определяет правила автозагрузки остальных классов
 */

class Init{

public $appConfigArr;
public $modConfigArr;
public $instance;
public $module = 'none';
public $root;

    function __construct($inst){
		$pathArr = split('/',$inst);
        if($pathArr[count($pathArr)-1] == 'controllers'){
            $inst = implode('/',array_slice($pathArr, 0, count($pathArr)-1));
            $pathArr = split('/',$inst);
        }
		
        if($pathArr[count($pathArr)-2] == 'modules'){
            $this->module = $pathArr[count($pathArr)-1];
			$inst = implode('/',array_slice($pathArr, 0,count($pathArr)-2));
		}
		
        $this->instance = $inst;
        $this->read_config();
        $this->loader_register();
        $this->root = '/'.$this->appConfArr['version']['name'];
        if ($this->appConfArr['version']['state'] == 'dev' || $this->appConfArr['version']['state'] == 'testing'){
            $this->root = $this->root.'/'.$this->appConfArr['version']['state'];
        }
        
    }

    function read_config(){
        $this->appConfArr = parse_ini_file($this->instance."/configuration/application.ini", true);
    
        if (!is_array($this->appConfArr)){
            echo <<<'EOT'
<html>
    <head>
        <title>Ошибка</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    </head>
    <body>
        Приложение не смогло прочесть конфигурацию.</br> 
        Дальнейшая работа невозможна
    </body>
</html>
EOT;
            exit();        
        }
        if ($this->module != 'none'){
            $this->modConfigArr = parse_ini_file($this->instance."/configuration/".$this->module.".ini", true);
        }
    } 
    
    function loader_register(){
        spl_autoload_register(array($this, 'loader'));
    }
    
    function loader($className){
        $className = strtolower($className);
        $file  = '';
        $fileM = '';
        if (substr($className, -6) == '_model'){
            $className = substr($className,0,strlen($className)-6);
            $file   = $this->instance.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.$className.'.model.php';
            if ($this->module != 'none'){
                $fileM = $this->instance.DIRECTORY_SEPARATOR.
                         'modules'.DIRECTORY_SEPARATOR.
                         $this->module.DIRECTORY_SEPARATOR.
                         'models'.DIRECTORY_SEPARATOR.
                         $className.'.model.php';
            }
        }
        else{
            $file = $this->instance.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.$className.'.class.php';
            if ($this->module != 'none'){
                $fileM = $this->instance.DIRECTORY_SEPARATOR.
                         'modules'.DIRECTORY_SEPARATOR.
                         $this->module.DIRECTORY_SEPARATOR.
                         'classes'.DIRECTORY_SEPARATOR.
                         $className.'.model.php';
            }
            
        }
        if (file_exists($file)){
            require_once ($file);
            return true;
        }
        if (file_exists($fileM)){
            require_once ($fileM);
            return true;
        }
        
        return false;
    }
    
    function get_db_config(){
        
        $ver = $this->appConfArr['version']['majver'] * 100 + 
               $this->appConfArr['version']['minver'] * 10 + 
               $this->appConfArr['version']['corrver'];
        
        $dbConfArr = array(
            'dbname'    => $this->appConfArr['version']['name'].
                           $ver.
                           $this->appConfArr['version']['state'],
            'host'      => $this->appConfArr['db']['host'],
            'user'      => $this->appConfArr['db']['user'],
            'password'  => $this->appConfArr['db']['password']
        );
        
        return $dbConfArr;
    }
    
    function get_login_config(){
		$ret = array(
			'isLoginReq' => $this->appConfArr['application']['login'],
			'defModule'  => $this->appConfArr['modules']['defmodule'],
			'appState'   => $this->appConfArr['version']['state'],
			'appName'    => $this->appConfArr['version']['name'],
            'instance'   => $this->instance
		);
		
		return $ret;
	}
    function is_logged(){
        $loginConfigArr = $this->get_login_config();
        if (!isset($_SESSION['user_id']) && ($loginConfigArr['isLoginReq'] == 1)){
            
            $lnk = $this->root.'/index.php';
            
            header("Location: ".$lnk); 
            exit();
		}
    
    }
    
}


