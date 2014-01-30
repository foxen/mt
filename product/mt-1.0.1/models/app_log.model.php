<?php
/**
 * app_log.model.php, mt-1.0.1
 * 
 * Модель определяет таблицы:
 * application_log
 * 
 */

class App_Log_Model extends Db{


    function __construct($dbConfArr){
        $this->set_db_Conf($dbConfArr);
        $this->create_tables();
        
    }
    
    function create_tables(){
        $createLogTxt = "create table if not exists app_log (
                            id int not null auto_increment,
                            event varchar(50),
                            description varchar(50),
                            dt date,
                            tm time,
                            primary key (id))";
        
        $this->my_query($createLogTxt);
        
    }
    
    function add_event($event, $description = ''){
        $insLogTxt ="insert into app_log (event, description, dt, tm) 
                        values ('".$event."', '".$description."', date(now()), time(now()))";
        
        //echo $insLogTxt;
        
        $this->my_query($insLogTxt);
    }
}


?>
