<?php

/**
 * mt-1.0.1,
 * netbooks
 * licenses.model.php
 * 
 * Модель определяет таблицы:
 * netbooks_licenses
 * netbooks_formats
 * 
 */
class Netbooks_Licenses_Model extends Db{


    function __construct($dbConfArr){
        $this->set_db_Conf($dbConfArr);
        $this->create_tables();
        $this->insert_defaults();
        
    }
    
    function create_tables(){
    	$createLicensesTxt = "create table if not exists 
                                netbooks_licenses (
                                    id int not null auto_increment,
                                    name varchar(50),
									is_paused bool default 0,
                                    is_taken bool default 0,
                                    is_deleted bool default 0,
                                    id_format int default 1,
                                    export_path varchar(100),
                                    primary key (id)
                                )";
        $this->my_query($createLicensesTxt);
        
        $createFormatsTxt = "create table if not exists 
                                netbooks_formats (
                                    id smallint not null auto_increment,
                                    format_name varchar(50),
                                    is_deleted bool default 0,
                                    primary key (id),
                                    unique(format_name)
                                )";
        $this->my_query($createFormatsTxt);
    }
    
    function insert_defaults(){
        $this->insert_format('amt');
    }
    
    function insert_license($name, $id_format = 1, $is_paused = 0, 
                                                    $exportPath = ''){
        
        $name        = $this->escape_string($name);
        $exportPath = $this->escape_string($exportPath);
        
        $insLicenseTxt = "insert into netbooks_licenses 
                                (name, id_format, is_paused, 
                                                        export_path) 
                            values 
                                ('".$name."', ".$id_format.", 
                                   ".$is_paused.", '".$exportPath."')";
        $this->my_query($insLicenseTxt);
        return $insLicenseTxt;
    }
    
    function insert_format($formatName){
		
		$formatName = $this->escape_string($formatName);
		
        $insTxt = "insert into netbooks_formats (format_name)
                        values ('".$formatName."') 
                    on duplicate key update is_deleted = 0";
        $this->my_query($insTxt);
    }
    
    function get_formats(){
		$queryTxt = "select id as formatid, 
							format_name as formatname 
						from netbooks_formats where is_deleted = 0";
		$ret = $this->my_query($queryTxt, 'JSON');
		return $ret;
	}
	
	function get_license_id ($licenseName){
		 $licenseName = $this->escape_string($licenseName);
		 $queryTxt = "select if(
						exists(select id from netbooks_licenses 
							where name like '".$licenseName."'	 
							and is_deleted = 0), 
						(select id from netbooks_licenses 
							where name like '".$licenseName."'	 
							and is_deleted = 0), 
						'none') as result";
		$ret = $this->my_query($queryTxt, 'ARR');
		return $ret;
	}
	
	function get_licenses(){
		$queryTxt = "select t1.id, 
							t1.name,
							t2.format_name, 
							if(t1.is_taken = 1, 'занята', 'свободна') as is_taken,
							if(t1.is_paused = 1, 'приостановлена', 'активна') as is_paused,
                            export_path
						from netbooks_licenses t1 
							left join 
						netbooks_formats t2 on t1.id_format = t2.id
						where t1.is_deleted = 0";
		$ret = $this->my_query($queryTxt, 'JSON');
		return $ret;
	}
	
	function get_licenses_agents(){
		$queryTxt = "select t1.id, 
							t1.name,
							t2.format_name, 
							if(t1.is_taken = 1, 'занята', 'свободна') as is_taken
						from netbooks_licenses t1 
							left join 
						netbooks_formats t2 on t1.id_format = t2.id
						where t1.is_deleted = 0 and t1.is_paused = 0";
		$ret = $this->my_query($queryTxt, 'JSON');
		return $ret;
	}
    
    function update_license($idLicense, 
                            $name, 
                            $id_format = 1, 
                            $is_paused = 0,
                            $exportPath = ''
                            ){
        
        $name        = $this->escape_string($name);
        $exportPath  = $this->escape_string($exportPath);
        $queryTxt = "update netbooks_licenses set 
                            name = '".$name."',
                            id_format = ".$id_format.",
                            is_paused = ".$is_paused.",
                            export_path = '".$exportPath."'
                        where id = ".$idLicense; 
                                
        $this->my_query($queryTxt);
    }
    
    function set_paused($idLicense){
        $queryTxt = "update netbooks_licenses set is_paused = 1 
                                                where id = ".$idLicense; 
        $this->my_query($queryTxt);
        
        $queryTxt = "update netbooks_agents set is_paused = 1 
                                        where id_license = ".$idLicense; 
        $this->my_query($queryTxt);
    }
    
    function set_active($idLicense){
        $queryTxt = "update netbooks_licenses set 
                            is_paused = 0 
                        where id = ".$idLicense; 
                                
        $this->my_query($queryTxt);
    }
    
    function set_deleted($idLicense){
        $queryTxt = "update netbooks_licenses set is_deleted = 1 
                                                where id = ".$idLicense; 
        $this->my_query($queryTxt);
        
        $queryTxt = "update_netbooks_agents set is_deleted = 1 
                                        where id_license = ".$idLicense;
        $this->my_query($queryTxt);
    }
    
    function set_taken($idLicense){
        $queryTxt = "update netbooks_licenses set 
                            is_taken = 1 
                        where id = ".$idLicense; 
                                
        $this->my_query($queryTxt);
    }
    
    function test_license($licenseName){
        $ret = 'invalid';
        $queryTxt = "select distinct 
                        id,export_path 
                    from 
                        netbooks_licenses 
                    where 
                        name like '".$licenseName."' 
                    and 
                        is_deleted = 0
                    and 
                        is_paused = 0
                    limit 1";
        $arr = $this->my_query($queryTxt,'ARR');
        if (is_array($arr)){
            $ret = $arr[0];
        }
        return $ret;
    }
}
 ?>
