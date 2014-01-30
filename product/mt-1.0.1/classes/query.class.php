<?php

/**
 * db.class.php, mt-1.0.1
 * 
 * Класс определяет абстрактные инструменты для
 * работы с базами данных mysql и mssql
 * 
 */
 
 class Query {
 
 
 
 
 }
 
 

class Query_1 {
    
    var $mysqlHost    = "myhost";
    var $mysqlUser    = "myuser";
    var $mysqlPass    = "mypass";
    var $mysqlBase    = "mybase";
    var $mysqlConnect = "none";
    
    var $mssqlHost    = "mshost";
    var $mssqlUser    = "msuser";
    var $mssqlPass    = "mspass";
    var $mssqlBase    = "msbase";
    var $mssqlConnect = "none";
    
    public function connectMysql(){
        if ($this->mysqlConnect == "none"){
            $this->mysqlConnect = mysql_connect($this->mysqlHost, 
                                                $this->mysqlUser, 
                                                $this->mysqlPass);
        }
        mysql_select_db($this->mysqlBase, $this->mysqlConnect);
        //mysql_set_charset('utf8',$this->mysqlConnect); 
    }
    
    public function closeMysql(){
        if ($this->mysqlConnect != "none"){
            mysql_close($this->mysqlConnect); 
        }
    }

    public function connectMssql(){
        if ($this->mssqlConnect == "none"){
            $this->mssqlConnect = mssql_connect($this->mssqlHost, 
                                                $this->mssqlUser, 
                                                $this->mssqlPass);
        }
        mssql_select_db($this->mssqlBase, $this->mssqlConnect);
    }
    
    public function closeMssql(){
        if ($this->mssqlConnect != "none"){
            mssql_close($this->mssqlConnect); 
        }
    }

    public function mysqlQuery($queryText, $output = "none",
                               $jroot = "rows"){
        $query = mysql_query($queryText, $this->mysqlConnect);
        $ret = "";
        if ($query){
            switch ($output){
            
                case "HTML":
                    $fields_num = mysql_num_fields($query);
                    $ret = "<table border='1'>";
                    $ret = $ret."<tr>";
                    for ($i=0; $i<$fields_num; ++$i){
                        $field = mysql_fetch_field($query,$i);
                        $ret = $ret."<td>".$field->name."</td>";
                    }	
                    $ret = $ret."</tr>";
                    while ($tmp_row = mysql_fetch_row($query)) {
                        $ret = $ret. "<tr>";
                        foreach ($tmp_row as $value){
                            $ret = $ret. "<td>";
                            $ret = $ret. $value ;
                            $ret = $ret. "</td>";
                        }
                        $ret = $ret. "</tr>";
                    }
                $ret = $ret."</table>";
                    break;
                
                case "JSON":
                    $arr = $this->mysqlQuery($queryText,"ARR");
                    $ret = "{\"".$jroot."\":".json_encode($arr).'}';
                    break;
                
                case "XML":
            
                    break;
                
                case "VAL":
                
            
                    break;
            
                case "ARR":
                    while ($row = mysql_fetch_array($query,MYSQL_ASSOC)) {
                        $ret[] = $row;
                    }
                    break;
            
                default:
                    $ret = $query;
            }
        }
        else{ $ret=false; }
        return $ret;
        mysql_free_result($query);
    }
    public function mssqlQuery($queryText, $output = "none", 
                               $jroot = "rows"){
        $query = mssql_query($queryText, $this->mssqlConnect);
        $ret = "";
        switch ($output){
            
            case "HTML":
                $fields_num = mssql_num_fields($query);
                $ret = "<table border='1'>";
                $ret = $ret."<tr>";
                for ($i=0; $i<$fields_num; ++$i){
                    $field = mssql_fetch_field($query,$i);
                    $ret = $ret."<td>". iconv('cp1251','UTF-8',$field->name)."</td>";
                }	
                $ret = $ret."</tr>";
                while ($tmp_row = mssql_fetch_row($query)) {
                    $ret = $ret. "<tr>";
                    foreach ($tmp_row as $value){
                        $ret = $ret. "<td>";
                        $ret = $ret.  iconv('cp1251','UTF-8',$value) ;
                        $ret = $ret. "</td>";
                    }
                    $ret = $ret. "</tr>";
                }
                $ret = $ret."</table>";
                break;
                
            case "JSON":
                $arr = $this->mssqlQuery($queryText,"ARR");
                $ret = "{".$jroot.":".json_encode($arr).'}';
                break;
                
            case "XML":
            
                break;
                
            case "VAL":
                $ret = "";
                while ($tmp_row = mssql_fetch_row($query)){
                    $row = "(";
                    foreach ($tmp_row as $value){
                        $row = $row."'".mysql_real_escape_string(iconv('cp1251','UTF-8',$value))."',";
                    }
                    $ret = $ret.substr($row,0,-1)."),";
                }
                $ret = substr($ret,0,-1);
                break;
            
            case "ARR":
                $ret = array();
                while ($row = mssql_fetch_array($query,MSSQL_ASSOC)) {
                    foreach($row as $key => $value){
                        $arr[iconv('cp1251','UTF-8',$key)] = iconv('cp1251','UTF-8',$value);
                    }
                    $ret[] = $arr;
                    $arr = NULL;
                }
                break;
            
            default:
                $ret = $query;
        }
        return $ret;
        mssql_free_result($query);
    }
}

?>
