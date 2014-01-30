<?php
/**
 * тnetbooks_agents.model.php, 
 * mt-1.0.1,
 * netbooks
 * 
 * модель представляющая агентов
 * 
 */
class Netbooks_Agents_Model extends Db{
    function __construct($dbConfArr){
        $this->set_db_Conf($dbConfArr);
        $this->create_tables();        
    }
    
    function create_tables(){
  		$createAgentsTxt = "create table 
                                if not exists 
                            netbooks_agents (
                            id int not null auto_increment,
                            name varchar(100),
                            id_agent varchar(20),
                            id_license int,
                            grp_names varchar(100),
                            is_debt bool default 0,
                            is_quantity bool default 0,
                            is_paused bool default 0,
                            is_deleted bool default 0,
                            primary key (id),
                            unique (id_agent, id_license))";
        $this->my_query($createAgentsTxt);
    }

    function insert_agent($name, 
                          $idAgent, 
                          $idLicense, 
                          $grpNames, 
                          $isDebt = 0, $isQuantity = 0, $isPaused =0){
        
        $name     = $this->escape_string($name);
        $grpNames = $this->escape_string($grpNames);
        
    	$insertAgentTxt = "insert into netbooks_agents (name, 
                                                        id_agent, 
                                                        id_license, 
                                                        grp_names, 
                                                        is_debt, 
                                                        is_quantity, 
                                                        is_paused) 
                                values('".$name."', '".
                                          $idAgent."', ".
                                          $idLicense.", '".
                                          $grpNames."', ".
                                          $isDebt.", ".
                                          $isQuantity." ,".
                                          $isPaused.")
                           on duplicate key update 
                                    name        = values(name),
                                    grp_names   = values(grp_names),
                                    is_debt     = values(is_debt),
                                    is_quantity = values(is_quantity),
                                    is_paused   = values(is_paused),
                                    is_deleted  = 0";
    	$this->my_query($insertAgentTxt);
    }
    
    function get_agents(){
        $queryTxt = "select 
                            t1.id, 
        
                            t2.name as license_name, 
                            
                            t1.name, 
                            
                            t1.id_agent, 
                            
                            t1.grp_names,
                             
                            if(t1.is_debt = 1, 
                               'показывать', 
                               'не показывать') as is_debt,
                               
                            if(t1.is_quantity = 1, 
                               'показывать', 
                               'не показывать') as is_quantity,
                               
                            if(t1.is_paused = 1,
                               'приостановлен',
                                'активен') as is_paused
                                
                        from
                            netbooks_agents t1 left join 
                            netbooks_licenses t2 on
                                t1.id_license = t2.id
                        where 
                            t1.is_deleted = 0";
                            
        $ret = $this->my_query($queryTxt,'JSON');
        
        return $ret;
    }
    
    function edit_agent($idAgent, $isDebt = 0, 
                                        $isQuantity = 0, $isPaused =0){
        
        $updateTxt = "update netbooks_agents set 
                                        is_debt     = ".$isDebt.", 
                                        is_quantity = ".$isQuantity.", 
                                        is_paused   = ".$isPaused."
                        where id = ".$idAgent;
        
    	$this->my_query($updateTxt);
    }
    
    function set_deleted($idAgent){
        $updateTxt = "update netbooks_agents set is_deleted = 1 
                                                where id = ".$idAgent;
        $this->my_query($updateTxt);
        
        $queryTxt = "SELECT count(id) as c from netbooks_agents 
                        where id_license = (
                            select id_license from netbooks_agents 
                                where id = ".$idAgent.") 
                        and is_deleted = 0";
        $arr = $this->my_query($queryTxt,"ARR");
        
        
        if($arr[0]['c']==0){
            $updateTxt = "update netbooks_licenses set is_taken = 0 
                            where id = (select id_license 
                                            from netbooks_agents 
                                        where id = ".$idAgent.")";
            $this->my_query($updateTxt);   
        }
    }
    
    function set_paused($idAgent){
        $updateTxt = "update netbooks_agents set is_paused = 1
                                                where id = ".$idAgent;
    	$this->my_query($updateTxt);
    }
    
    function set_started($idAgent){
        $updateTxt = "update netbooks_agents set is_paused = 0
                                                where id = ".$idAgent;
    	$this->my_query($updateTxt);
    }
    
 }
