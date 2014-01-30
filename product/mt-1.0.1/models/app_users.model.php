<?php
/**
 * app_users.model.php, mt-1.0.1
 * 
 * Модель определяет таблицы:
 * app_users
 * 
 */

class App_Users_Model extends Db{
    function __construct($dbConfArr){
        $this->set_db_Conf($dbConfArr);
        $this->create_tables();
        $this->insert_defaults();
        
    }
    
    function create_tables(){
        $createUsersTxt = "create table if not exists app_users (
                            id int not null auto_increment,
                            login varchar(50) not null default '',
                            password varchar(32) not null default '',
                            name varchar(50),
                            salt varchar(3) not null default 'fox',
                            created datetime,
                            grp smallint(8),
                            is_deleted bool default 0,
                            primary key (id))";
        
        $this->my_query($createUsersTxt);
        
        $createGrpTxt = "create table if not exists app_groups (
							id int not null auto_increment,
							name varchar(20) not null,
							is_deleted bool default 0,
							primary key (id))";
        $this->my_query($createGrpTxt);
    }
    
    function insert_defaults(){
		$testAdminTxt = "select if((select login from app_users where id = 1) = 'admin', 'exists', 'noexists') as res";
        $res = $this->my_query($testAdminTxt,"ARR");
        if ($res[0]['res']=='noexists'){
            $this->insert_user('admin', '943651', 'global admin', 1);
        }
        
        $testUserTxt = "select if((select login from app_users where id = 2) = 'user', 'exists', 'noexists') as res";
        $res = $this->my_query($testUserTxt,"ARR");
        if ($res[0]['res']=='noexists'){
            $this->insert_user('user', '123', 'пользователь', 2);
        }
        
        $grpInsTxt = "insert into app_groups (id, name) values (1, 'admins'),(2, 'users') on duplicate key update name = values(name)";
        $this->my_query($grpInsTxt);
	}
    function insert_user($login, $password, $name ='', $grp = 2){
		$salt = chr(rand(33,126)).chr(rand(33,126)).chr(rand(33,126));
		
		$password = md5(md5($password).$salt);
		
		$insertUserTxt ="insert into app_users (login, password, name, salt, created, grp) 
							values ('".$login."', '".$password."', '".$name."', '".$salt."', now(), ".$grp.")";
		
		$this->my_query($insertUserTxt);
	}
    
    function test_user($login, $password){
        $ret['res'] = false;
        
		$getUserTxt = "select id, password, salt from app_users where login like '".$login."' and is_deleted = 0 limit 1";
		
		$res = $this->my_query($getUserTxt, 'ARR');
		
		if (is_array($res)){
			if( $res[0]['password'] == md5(md5($password).$res[0]['salt'])){
                $ret['res'] = true;
                $ret['id'] = $res[0]['id'];
			}
		}
		
		return $ret;
	}
	
	function get_user_name($userId){
		
		$getUserNameTxt = "select name from app_users where id = ".$userId;
		
		$res = $this->my_query($getUserNameTxt, "JSON");
		
		return $res;
	}
	
    function get_user_grp($userId){
        $ret = 'none';
        
        $getGrpTxt = "SELECT t2.name as grpname FROM app_users t1 left join app_groups t2 on t1.grp = t2.id where t1.id =".$userId." and t1.is_deleted = 0 and t2.is_deleted = 0";
        $res = $this->my_query($getGrpTxt, "ARR");
        if(is_array($res[0])){
            $ret = $res[0]['grpname'];
        }
        
        return $ret;
    }
	
}


?>
