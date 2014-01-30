<?php
/**
 * modules/dashboard/app_log.model.php, mt-1.0.1
 * 
 * Модель определяет таблицы:
 * dashboard_ur
 * 
 */
 class Dashboard_Ur_Model extends Db{
 
    function __construct($dbConfArr){
        $this->set_db_Conf($dbConfArr);
        $this->create_tables();
        $this->insert_defaults();
    }
    
    function create_tables(){
        $createUrTxt = "create table if not exists dashboard_ur (
                            id int not null auto_increment,
                            obj_class varchar(50),
                            object varchar(50) not null,
                            user_id int not null,
                            is_deleted bool default 0,
                            primary key (id))";
        $this->my_query($createUrTxt);
    }
    
    function insert_defaults(){
        $this->add_allowed_module(2, 'client');
    }
    
    function add_allowed_module($userId, $moduleName){
        $testTxt = "select if(
                        (select object from dashboard_ur 
                            where user_id = ".$userId." and obj_class like 'module widget') = '".$moduleName."',
                                'alrady', 
                                'none') as mdl";
                                
        $res = $this->my_query($testTxt,"ARR");
        
        if ($res[0]['mdl'] == 'none'){
            $addAllowedModTxt = "insert into dashboard_ur (obj_class, object, user_id) values ('module widget', '".$moduleName."', ".$userId.")";
            $this->my_query($addAllowedModTxt);
        }
    }
    
    function get_allowed_modules($userId){
        $ret = 'none';
        $objAppUsersModel = new App_Users_Model($this->dbConfigArr);
        //return $objAppUsersModel->get_user_grp($userId);
        if ($objAppUsersModel->get_user_grp($userId) != 'admins'){
            $getModulesTxt = "select object from dashboard_ur where obj_class like 'module widget' and user_id = ".$userId." and is_deleted = 0";
            $ret = $this->my_query($getModulesTxt,"ARR");
        }
        if ($objAppUsersModel->get_user_grp($userId) == 'admins'){
            $ret = 'all';
        }
        return $ret;
    } 
    
}
 ?>
