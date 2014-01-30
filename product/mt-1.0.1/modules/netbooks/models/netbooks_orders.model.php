<?php

/**
 * mt-1.0.1,
 * netbooks
 * orders.model.php
 * 
 * Модель определяет таблицы:
 * netbooks_orders
 * 
 */
class Netbooks_Orders_Model extends Db{


    function __construct($dbConfArr){
        $this->set_db_Conf($dbConfArr);
        $this->create_tables();
    }
    
    function create_tables(){
    	$createTxt = "create table if not exists netbooks_orders (
                        id int not null auto_increment,
                        id_license smallint,
                        ident varchar(20),
                        idc smallint,
                        id_client int,
                        id_agent int,
                        dt date,
                        tm time,
                        d_date date,
                        total decimal(10,4),
                        comment varchar(200),
                        is_deleted bool default 0,
                        is_exported bool default 0,
                        primary key(id),
                        index (ident, idc))";
        $this->my_query($createTxt);
        
        $createTxt = "create table 
                        if not exists 
                      netbooks_orders_details (
                        id int not null auto_increment,
                        ident varchar(20),
                        idc smallint,
                        id_product int,
                        amount int,
                        is_deleted bool default 0,
                        primary key(id),
                        index (ident, idc))";
        $this->my_query($createTxt);
    }
    
    function load_orders($contentArr,$ident,$idLicense){
		
		if(isset($contentArr[0]) && is_array($contentArr[0])){
			$valsArr = array();
			foreach($contentArr[0] as $row){
				$val = "(".$idLicense .", 
						'".$ident     ."', 
						 ".$row['i']  .", 
						 ".$row['c']  .",
						 ".$row['a']  .",
						'".$row['d']  ."',
						'".$row['t']  ."',
						'".$row['dd'] ."',
						 ".$row['ttl'].",
						'".$row['cmt']."')";
				$valsArr[] = $val;
			}
			$valsHeads = implode(',',$valsArr);
		}
		
		if(isset($contentArr[1]) && is_array($contentArr[1])){
			$valsArr = array();
			foreach($contentArr[1] as $row){
				$val = "('".$ident   ."', 
						  ".$row['i'].", 
						  ".$row['p'].",
						  ".$row['a'].")";
				$valsArr[] = $val;
			}
			$valsDet = implode(',',$valsArr);
		}
		
		$insTxt = "insert into netbooks_orders (id_license, ident, idc, id_client, id_agent,
												dt, tm, d_date, total, comment) values ".$valsHeads;
		$this->my_query($insTxt);
		
		$insTxt = "insert into netbooks_orders_details (ident, idc, id_product, amount) values ".$valsDet;
		$this->my_query($insTxt);
		
	}
    
    function export_amt($rnd, $licPath){
        $queryTxt = "select 
                        id,
                        id_license,
                        ident,
                        idc,
                        id_client,
                        id_agent,
                        date_format(dt,'%d.%m.%y') as dt,
                        tm,
                        if(d_date = '0000-00-00',
                           date_format(dt,'%d.%m.%y'),
                           date_format(d_date,'%d.%m.%y')) as d_date,
                        round(total,2) as total,
                        comment
                    from netbooks_orders where 
                        ident like '".$rnd."' and
                        is_exported = 0 and is_deleted = 0";
        $arrO = $this->my_query($queryTxt,'ARR');
        
        foreach($arrO as $row){
            $id        = $row['id'];
            $idLicense = $row['id_license'];
            $ident     = $row['ident'];
            $idc       = $row['idc'];
            $idClient  = $row['id_client'];
            $idAgent   = $row['id_agent'];
            $dt        = $row['dt'];
            $tm        = $row['tm'];
            $dDate     = $row['d_date'];
            $ttl	   = $row['total'];
            $total     = str_replace('.',',',"$ttl");
            $comment   = $row['comment'];
            
            $queryTxt = "select name from netbooks_licenses 
                                            where id = ".$idLicense;
            $arr = $this->my_query($queryTxt,'ARR');
            
            $licenseName = $arr[0]['name'];
            
            $queryTxt = "select alias from netbooks_".$idAgent."_clients 
                            where id_client = ".$idClient." 
                            order by id desc
                            limit 1";
            $arr = $this->my_query($queryTxt,'ARR');
            
            $clientName = $arr[0]['alias'];
            
            $queryTxt = "select name from netbooks_agents 
                            where id_agent = ".$idAgent." 
                            order by id desc
                            limit 1";
            $arr = $this->my_query($queryTxt,'ARR');
            
            $agentName = $arr[0]['name'];
            
$content = <<<EOD
Документ;Заказ МТ
Номер;$licenseName-$id
Дата;$dt
Дата доставки;$dDate
Покупатель;$clientName;$idClient
Торговый;$agentName;$idAgent
Сумма;$total
Примечание;$comment
Версия;mt-1.0.1
Сформирован;$dt $tm
Код;Количество

EOD;
            $queryTxt = "select id_product, amount from
                            netbooks_orders_details where
                            ident like '".$ident."' and
                            idc = ".$idc." and is_deleted = 0";
            $arr = $this->my_query($queryTxt,'ARR');
$rows = '';
            foreach($arr as $r){
$rows = $rows.$r['id_product'].';'.$r['amount'].'
';
            }
            
            $content = $content.$rows;
            $content = iconv("UTF-8","cp1251",$content);
            
            $fn = $licPath.$id.'.amt';
            
            $f = fopen($fn,"w");
            fclose($f);
            file_put_contents($fn,$content);
        }
        $updTxt = "update netbooks_orders set is_exported = 1 where 
                    ident like '".$rnd."'";
        $this->my_query($updTxt);
    }
}
 ?>
