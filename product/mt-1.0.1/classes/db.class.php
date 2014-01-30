<?php
/**
 * db.class.php, mt-1.0.1
 * 
 * Класс определяет свойства и методы работы с базами данных. 
 * Поскольку СУБД MySql используется для хранения данных приложения,
 * при вызове метда mysqlQuery создается (если не создана) база
 * данных приложения.
 * 
 */
class Db {
    var $dbConfigArr;
    
    var $dbName;
    var $host;
    var $user;
    var $password;
    
    var $msDbName;
    var $msHost;
    var $msUser;
    var $msPassword;
    
    function set_db_Conf($dbConfArr, $type ='mysql'){
        $this->dbConfigArr = $dbConfArr;
        switch ($type){
            case 'mysql':
                $this->dbName   = $dbConfArr['dbname'];
                $this->host     = $dbConfArr['host'];
                $this->user     = $dbConfArr['user'];
                $this->password = $dbConfArr['password'];
                break;
            case 'mssql':
                $this->msDbName   = $dbConfArr['msdbname'];
                $this->msHost     = $dbConfArr['mshost'];
                $this->msUser     = $dbConfArr['msuser'];
                $this->msPassword = $dbConfArr['mspassword'];
                break;
        }
    }
    
    function escape_string($string){
		$connect = mysql_connect($this->host, $this->user, $this->password);
		$ret = mysql_real_escape_string($string);
		mysql_close($connect);
		return $ret;
	}
    
    function my_query($queryTxt, $output = 'none', $jroot = 'rows'){
        $connect = mysql_connect($this->host, $this->user, $this->password);

        $createDbTxt = "create database if not exists ".$this->dbName.
                       " character set = 'utf8'";
        
        mysql_query($createDbTxt, $connect);
        
        mysql_select_db($this->dbName, $connect);
             
        $query = mysql_query($queryTxt, $connect);
        
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
                    $arr = array();
                    while ($row = mysql_fetch_array($query,MYSQL_ASSOC)) {
                        $arr[] = $row;
                    }
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
                case "F_ARR":
                    $fArr = array();
                    while ($row = mysql_fetch_array($query,MYSQL_ASSOC)) {
                        foreach($row as $value){
                            $fArr[] =  $value;
                        }
                        $ret = $fArr;
                    }
                    break;
            
                default:
                    $ret = $query;
            }
            
            if(is_resource($query)){
                mysql_free_result($query);
            }
            
            mysql_close($connect);
        }
        else{ $ret=false; }
        
        
        
        return $ret;
        
       
    }
    
    function ms_query($queryTxt, $output = 'none', $jroot = 'rows'){
        $connect = mssql_connect($this->msHost, $this->msUser, $this->msPassword);
        
        mssql_select_db($this->msDbName, $connect);
                
        $query = mssql_query($queryTxt, $connect);
        
        $ret = "";
        
        if ($query){
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
                $arr = $this->ms_query($queryTxt,"ARR");
                $ret = "{".$jroot.":".json_encode($arr).'}';
                break;
                
            case "XML":
                break;
                
            case "VAL":
                $ret = "";
                $my_connect = mysql_connect($this->host, $this->user, $this->password);
                while ($tmp_row = mssql_fetch_row($query)){
                    $row = "(";
                    foreach ($tmp_row as $value){
                        $row = $row."'".mysql_real_escape_string(iconv('cp1251','UTF-8',$value))."',";   
                    }
                    $ret = $ret.substr($row,0,-1)."),";    
                }
                mysql_close($my_connect);
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
            
            if(is_resource($query)){
                mssql_free_result($query);
            }
            
            mssql_close($connect);
        }
        else{ $ret=false; }
        
        
        
        return $ret;
    }

    function cp1251_to_utf8 ($txt)  {
      $in_arr = array (
          chr(208), chr(192), chr(193), chr(194),
          chr(195), chr(196), chr(197), chr(168),
          chr(198), chr(199), chr(200), chr(201),
          chr(202), chr(203), chr(204), chr(205),
          chr(206), chr(207), chr(209), chr(210),
          chr(211), chr(212), chr(213), chr(214),
          chr(215), chr(216), chr(217), chr(218),
          chr(219), chr(220), chr(221), chr(222),
          chr(223), chr(224), chr(225), chr(226),
          chr(227), chr(228), chr(229), chr(184),
          chr(230), chr(231), chr(232), chr(233),
          chr(234), chr(235), chr(236), chr(237),
          chr(238), chr(239), chr(240), chr(241),
          chr(242), chr(243), chr(244), chr(245),
          chr(246), chr(247), chr(248), chr(249),
          chr(250), chr(251), chr(252), chr(253),
          chr(254), chr(255)
      );  
   
      $out_arr = array (
          chr(208).chr(160), chr(208).chr(144), chr(208).chr(145),
          chr(208).chr(146), chr(208).chr(147), chr(208).chr(148),
          chr(208).chr(149), chr(208).chr(129), chr(208).chr(150),
          chr(208).chr(151), chr(208).chr(152), chr(208).chr(153),
          chr(208).chr(154), chr(208).chr(155), chr(208).chr(156),
          chr(208).chr(157), chr(208).chr(158), chr(208).chr(159),
          chr(208).chr(161), chr(208).chr(162), chr(208).chr(163),
          chr(208).chr(164), chr(208).chr(165), chr(208).chr(166),
          chr(208).chr(167), chr(208).chr(168), chr(208).chr(169),
          chr(208).chr(170), chr(208).chr(171), chr(208).chr(172),
          chr(208).chr(173), chr(208).chr(174), chr(208).chr(175),
          chr(208).chr(176), chr(208).chr(177), chr(208).chr(178),
          chr(208).chr(179), chr(208).chr(180), chr(208).chr(181),
          chr(209).chr(145), chr(208).chr(182), chr(208).chr(183),
          chr(208).chr(184), chr(208).chr(185), chr(208).chr(186),
          chr(208).chr(187), chr(208).chr(188), chr(208).chr(189),
          chr(208).chr(190), chr(208).chr(191), chr(209).chr(128),
          chr(209).chr(129), chr(209).chr(130), chr(209).chr(131),
          chr(209).chr(132), chr(209).chr(133), chr(209).chr(134),
          chr(209).chr(135), chr(209).chr(136), chr(209).chr(137),
          chr(209).chr(138), chr(209).chr(139), chr(209).chr(140),
          chr(209).chr(141), chr(209).chr(142), chr(209).chr(143)
      );  
   
      $txt = str_replace($in_arr,$out_arr,$txt);
      return $txt;
    }
    
    function txt_from_ms($txt,$connect){
        //$ret = "'".mysql_real_escape_string($this->cp1251_to_utf8($txt),$connect)."'";
        $txt = str_replace("'"," ",$txt);
        $ret = "'".mysql_real_escape_string(iconv('cp1251', 'utf8',$txt),$connect)."'";
        return $ret;
    }
    
    function nmb_null($v){
		$ret = $v;
		if ($v == null or $v == 'null' or $v == 'NULL' or $v == ''){
			$ret = 0;
		}
		return $ret;
	}
}

?>
