<?php
/**
 * netbooks_agents_ms.model.php, 
 * mt-1.0.1,
 * netbooks
 * 
 * модель представляющая агентов
 * с сервера ms sql
 */
class Netbooks_Agents_Ms_Model extends Db{
    function __construct($dbConfArr, $msDbConfArr){
        $this->set_db_Conf($dbConfArr);
        $this->set_db_Conf($msDbConfArr,'mssql');

        $this->create_tables();        
    }
    
    function create_tables(){
  	
    }

    function get_agents(){
    	$rnd = rand(33,126);

    	$queryMs1 = "select distinct a.TraderID, alias, groupname 
                    	from vscheme a, (
	                    	select vtradersroutes.traderid, alias from  
                    		vtradersroutes left join vtraders  
                    		on vtraders.clientid = vtradersroutes.traderid
                    			where vtradersroutes.VisitsSchedule IS NOT NULL 
                    		group by TraderId, alias
                    	) b
                    	where a.traderid=b.traderid
                    	order by a.traderid";
        
        $queryMs = "select distinct a.TraderID, alias, groupname 
                        from vscheme a, (
                                select distinct clientid, alias from vtraders  
                        ) b
                        where a.traderid=b.clientid
                        order by a.traderid";
        
        $queryMy1 = "(select id, alias, GROUP_CONCAT(' ',grp) as grp, 'не привязан' as taken
                    from netbooks_agents_ms_tmp".$rnd." where id not in 
                    (select distinct id_agent from netbooks_agents 
                    where is_deleted = 0) group by id order by alias)
                    union
                    (select id, alias, GROUP_CONCAT(' ',grp) as grp, 'уже привязан' as taken
                    from netbooks_agents_ms_tmp".$rnd." where id in 
                    (select distinct id_agent from netbooks_agents 
                    where is_deleted = 0) group by id order by alias)
                    ";
        $queryMy = "select id, alias, GROUP_CONCAT(' ',grp) as grp 
                    from netbooks_agents_ms_tmp".$rnd." group by id order by alias";
        
        $createTmpAgents = "create table netbooks_agents_ms_tmp".$rnd." 
                            (id varchar(5), 
                            alias varchar(100), 
                            grp varchar (200))";
        
        $dropTmpAgents = "drop table netbooks_agents_ms_tmp".$rnd."";
    
        $insertTmpAgents = "insert into netbooks_agents_ms_tmp".$rnd." 
                            (id, alias, grp) values ";
        
        $this->my_query($dropTmpAgents);
        $this->my_query($createTmpAgents);
        $val = $this->ms_query($queryMs,'VAL');
        $this->my_query($insertTmpAgents.$val);
        //$ret = $this->my_query($queryMy1,'ARR');
        $ret = $this->my_query($queryMy1,'JSON'); //раскомментировать при запуске!
        $this->my_query($dropTmpAgents);
        return $ret;                 
    }

    function sync_agents($idLicense, $pth){
        $start = microtime(true);
	$logFile = "/var/www/mt/product/mt-1.0.1/modules/netbooks/models/log.txt";
        $connect = mysql_connect($this->host, $this->user, $this->password);
        mysql_select_db($this->dbName, $connect);

        $createAgentsTxt = "create temporary table netbooks_tmp_agents (
                                    id_agent int not null,
                                    alias varchar(100),
                                    primary key (id_agent))";
        mysql_query($createAgentsTxt, $connect);

        $createSchmeTxt = "create temporary table netbooks_tmp_scheme (
                                    id_agent int,
                                    id_client int, 
                                    id_grp smallint)";
        mysql_query($createSchmeTxt, $connect);
        
        $createClientsTxt = "create temporary table netbooks_tmp_clients (
                                    id_client int not null,
                                    alias varchar(100),
                                    shortname varchar(100),
                                    address varchar(100),
                                    inn varchar(12),
                                    type tinyint,
                                    xcoord decimal(15,13),
                                    ycoord decimal(15,13),
                                    id_price smallint,
                                    clmn tinyint,
                                    debt decimal(14,4) default 0,
                                    primary key (id_client))";
        mysql_query($createClientsTxt, $connect);

        $createRoutesTxt = "create temporary table netbooks_tmp_routes (
                                    id_agent int,
                                    id_client int,
                                    visit tinyint)";
        mysql_query($createRoutesTxt, $connect);

        $createGroupsTxt = "create temporary table netbooks_tmp_groups (
                                    id_grp smallint not null,
                                    name varchar(50),
                                    primary key (id_grp))";
        mysql_query($createGroupsTxt, $connect);

        $createProductsTxt = "create temporary table netbooks_tmp_products (
                                    id_product int not null,
                                    id_grp smallint,
                                    name varchar(100),
                                    quantity int,
                                    base_price decimal(10,4),
                                    primary key (id_product))";
        mysql_query($createProductsTxt, $connect);

        $createDiscountsProdsTxt = "create temporary table netbooks_tmp_discounts_prods (
                                        id_product int, 
                                        id_price smallint,
                                        col tinyint,
                                        discount decimal(10,4))";
        mysql_query($createDiscountsProdsTxt, $connect);

        $createDiscountsGrpsTxt = "create temporary table netbooks_tmp_discounts_grps (
                                        id_grp int, 
                                        id_price smallint,
                                        col tinyint,
                                        discount decimal(10,4))";
        mysql_query($createDiscountsGrpsTxt, $connect);

        $agentsArr = array();
        $getAgentsArrTxt = " select distinct id_agent 
                                from netbooks_agents 
                                where id_license = ".$idLicense." and 
                                is_deleted = 0";
        $query = mysql_query($getAgentsArrTxt, $connect);
        while ($row = mysql_fetch_array($query,MYSQL_ASSOC)){
            $agentsArr[] =  $row['id_agent'];
        }
        

        $mConnect = mssql_connect($this->msHost, $this->msUser, $this->msPassword, true);
        mssql_select_db($this->msDbName, $mConnect);

        $insAgentsIdsTxt = "insert into #tmp_agents_ids values (".implode("); insert into #tmp_agents_ids values (",$agentsArr).");";
	$creatingTmp = microtime(true);
        $tm = $creatingTmp - $start;
//	file_put_contents($logFile, " start cratingTmp ".$tm, FILE_APPEND);

        $msQueryTxt = <<<EOD
create table #tmp_agents_ids (id_agent int not null);
$insAgentsIdsTxt


create table #tmp_agents (
   id_agent int not null,
   alias varchar(50));

insert into #tmp_agents
select clientid, alias from 
vtraders where clientid in (select * from #tmp_agents_ids);

create table #tmp_scheme (
   id_agent int,  
   id_client int,
   id_grp smallint,);

insert into #tmp_scheme
select t1.traderid, t1.customerid, t2.groupid from vscheme t1
left join vgroups t2 on t1.groupname = t2.groupname
where t1.traderid in (select * from #tmp_agents_ids);

create table #tmp_clients (
   cntr int IDENTITY(1,1) NOT NULL,
   id_client int not null,
   alias varchar(100),
   shortname varchar(100) null,
   address varchar(100) null,
   inn varchar(12) null,
   type tinyint null,
   xcoord decimal(15,13) null,
   ycoord decimal(15,13) null,
   id_price smallint,
   clmn tinyint,
   debt int null);

insert into #tmp_clients (id_client,alias,shortname,address,inn,type,xcoord,ycoord,id_price,clmn,debt)
select t1.clientid, 
       t1.alias,
       t1.shortname,
       t1.address,
       t1.inn,
       t1.type,
       t1.xcoord,
       t1.ycoord,
       t1.priceid,
       t1.pricecolumn,
       t2.IsDelayed from vclients t1 left join (select clientid, 
                                                       max(CAST(IsDelayed as int)) as isdelayed 
                                                from __AMTBalances 
                                                group by clientid) t2 
on t1.clientid = t2.clientid where t1.clientid in 
    (select distinct id_client from #tmp_scheme) 
and t1.priceid is not null and t1.pricecolumn is not null;

create table #tmp_routes (
   id_agent int,
   id_client int,
   visit tinyint);

insert into #tmp_routes
select traderid, clientid, visitsschedule
from vtradersroutes where traderid in
(select * from #tmp_agents_ids);

create table #tmp_groups (
   id_grp smallint not null,
   name varchar(50));

insert into #tmp_groups
select * from vgroups where groupid in
(select distinct id_grp from #tmp_scheme);

create table #tmp_products (
    id_product int not null,
    id_grp smallint,
    name varchar(100),
    quantity int,
    base_price decimal(10,4));
insert into #tmp_products
select productid,
       groupid,
       productname,
       quantity,
       baseprice from vproducts where groupid in
(select distinct id_grp from #tmp_groups)
and quantity > 0;

create table #tmp_discounts_products(id_product int, id_price int, discount decimal(10,4), col tinyint);

create table #tmp_discounts_groups(id_group int, id_price int, discount decimal(10,4), col tinyint);

declare @c1 tinyint;
set @c1 = 1;
declare @c2 tinyint;
set @c2 = 2;
declare @c3 tinyint;
set @c3 = 3;
declare @c4 tinyint;
set @c4 = 4;

insert into #tmp_discounts_products
select itemid, priceid, column1 as discount, @c1 as col  from vdiscounts where isproduct = 1 and column1 is not null and column1<>0
union
select itemid, priceid, column2 as discount, @c2 as col  from vdiscounts where isproduct = 1 and column2 is not null and column2<>0
union
select itemid, priceid, column3 as discount, @c3 as col  from vdiscounts where isproduct = 1 and column3 is not null and column3<>0
union
select itemid, priceid, column4 as discount, @c4 as col  from vdiscounts where isproduct = 1 and column4 is not null and column4<>0;

insert into #tmp_discounts_groups
select itemid, priceid, column1 as discount, @c1 as col  from vdiscounts where isproduct = 0 and column1 is not null and column1<>0
union
select itemid, priceid, column2 as discount, @c2 as col  from vdiscounts where isproduct = 0 and column2 is not null and column2<>0
union
select itemid, priceid, column3 as discount, @c3 as col  from vdiscounts where isproduct = 0 and column3 is not null and column3<>0
union
select itemid, priceid, column4 as discount, @c4 as col  from vdiscounts where isproduct = 0 and column4 is not null and column4<>0;

create table #tmp_prices (id_grp int, id_price int, col tinyint); 
insert into #tmp_prices (id_grp, id_price, col) 
select distinct t1.id_grp, t2.id_price, t2.clmn from 
(select distinct id_client, id_grp from #tmp_scheme) t1
left join #tmp_clients t2 on t1.id_client = t2.id_client
where t2.id_price is not null and t2.clmn is not null;

create table #tmp_prices_products(id_product int, id_price int, col tinyint)

insert into #tmp_prices_products
select t1.id_product, t2.id_price, t2.col from #tmp_products t1, #tmp_prices t2 where t1.id_grp = t2.id_grp; 

create table #tmp_dis_products(id_product int, id_price int, col tinyint, discount decimal(10,4));

create table #tmp_dis_groups(id_group int, id_price int, col tinyint, discount decimal(10,4));

insert into #tmp_dis_products
select t1.id_product, t1.id_price, t1.col, t2.discount from #tmp_prices_products t1,#tmp_discounts_products t2
where t1.id_product = t2.id_product and t1.id_price = t2.id_price and t1.col = t2.col;

insert into #tmp_dis_groups
select t1.id_grp, t1.id_price, t1.col, t2.discount from #tmp_prices t1,#tmp_discounts_groups t2
where t1.id_grp = t2.id_group and t1.id_price = t2.id_price and t1.col = t2.col;

select * from #tmp_agents;

EOD;
        $mQuery = mssql_query($msQueryTxt, $mConnect);
        while ($row = mssql_fetch_array($mQuery,MSSQL_ASSOC)) {
            $idAgent = $row['id_agent'];
            $alias = $this->txt_from_ms($row['alias'],$connect);
            mysql_query("insert into netbooks_tmp_agents values (".$idAgent.", ".$alias.");", $connect);
        }
        mssql_free_result($mQuery);

	$getMs = microtime(true);
        $tm = $getMs - $creatingTmp;
//	file_put_contents($logFile, " getMs ".$tm, FILE_APPEND);

        $msQueryTxt = "select * from #tmp_scheme";
        $mQuery = mssql_query($msQueryTxt, $mConnect);
        $ins = array();
	while ($row = mssql_fetch_array($mQuery,MSSQL_ASSOC)) {
            //mysql_query("insert into netbooks_tmp_scheme values (".$row['id_agent'] .", ".
            //                                                       $row['id_client'].", ".
            //                                                       $row['id_grp']   .")", $connect);
	    $ins[] = "(".implode(",",$row).")";
        }
        mssql_free_result($mQuery);
	$mysql_txt = "insert ignore into netbooks_tmp_scheme values ".implode(",",$ins);
	mysql_query($mysql_txt,$connect);
	
	$insertTmpSch = microtime(true);
        $tm = $insertTmpSch - $getMs;
//	file_put_contents($logFile, " inserting scheme ".$tm, FILE_APPEND);
        

        $msQueryTxt = "select * from #tmp_clients";
        $mQuery = mssql_query($msQueryTxt, $mConnect);
        $ins = array();
	while ($row = mssql_fetch_array($mQuery,MSSQL_ASSOC)) {
//            mysql_query("insert into netbooks_tmp_clients values (".$row['id_client']                             .", ".
//                                                                    $this->txt_from_ms($row['alias'],$connect)    .", ".
//                                                                    $this->txt_from_ms($row['shortname'],$connect).", ".
//                                                                    $this->txt_from_ms($row['address'],$connect)  .", ".  
//                                                                    $this->txt_from_ms($row['inn'],$connect)      .", ".
//                                                                    $this->nmb_null($row['type'])                 .", ".
//                                                                    $this->nmb_null($row['xcoord'])               .", ".
//                                                                    $this->nmb_null($row['ycoord'])               .", ".
//                                                                    $row['id_price']                              .", ".
//                                                                    $row['clmn']                                  .", ".
//                                                                    $this->nmb_null($row['debt'])                 .")", $connect);
	    $ins[] = "(".$row['id_client']                        .", ".
                    $this->txt_from_ms($row['alias'],$connect)    .", ".
                    $this->txt_from_ms($row['shortname'],$connect).", ".
                    $this->txt_from_ms($row['address'],$connect)  .", ".  
                    $this->txt_from_ms($row['inn'],$connect)      .", ".
                    $this->nmb_null($row['type'])                 .", ".
                    $this->nmb_null($row['xcoord'])               .", ".
                    $this->nmb_null($row['ycoord'])               .", ".
                    $row['id_price']                              .", ".
                    $row['clmn']                                  .", ".
                    $this->nmb_null($row['debt'])                 .")";
        }
        mssql_free_result($mQuery);
	$mysql_txt = "insert ignore into netbooks_tmp_clients values ".implode(",",$ins);
        $a = mysql_query($mysql_txt,$connect);
	$b = mysql_error($connect);

	$insertTmpCln = microtime(true);
        $tm = $insertTmpCln - $insertTmpSch;
//	file_put_contents($logFile, " inserting clients ".$tm.$b, FILE_APPEND);

        $msQueryTxt = "select * from #tmp_routes";
        $mQuery = mssql_query($msQueryTxt, $mConnect);
        while ($row = mssql_fetch_array($mQuery,MSSQL_ASSOC)) {
            mysql_query("insert into netbooks_tmp_routes values (".$row['id_agent'] .", ".
                                                                   $row['id_client'].", ".
                                                                   $row['visit']   .")", $connect);
        }
        mssql_free_result($mQuery);

	$insertTmpRts = microtime(true);
        $tm = $insertTmpRts - $insertTmpCln;
//	file_put_contents($logFile, " inserting routes ".$tm, FILE_APPEND);


        $msQueryTxt = "select * from #tmp_groups";
        $mQuery = mssql_query($msQueryTxt, $mConnect);
        while ($row = mssql_fetch_array($mQuery,MSSQL_ASSOC)) {
            mysql_query("insert into netbooks_tmp_groups values (".$row['id_grp']                           .", ".
                                                                   $this->txt_from_ms($row['name'],$connect).")", $connect);
        }
        mssql_free_result($mQuery);

	$insertTmpGrp = microtime(true);
        $tm = $insertTmpGrp - $insertTmpRts;
//	file_put_contents($logFile, " inserting groups ".$tm, FILE_APPEND);

        $ins = array();
        $msQueryTxt = "select * from #tmp_products";
        $mQuery = mssql_query($msQueryTxt, $mConnect);
        while ($row = mssql_fetch_array($mQuery,MSSQL_ASSOC)) {
            //mysql_query("insert into netbooks_tmp_products values (".$row['id_product']                       .", ".
            //                                                         $row['id_grp']                           .", ".
            //                                                         $this->txt_from_ms($row['name'],$connect).", ".
            //                                                         $row['quantity']                         .", ".
            //                                                         $row['base_price']                       .")", $connect);
	    $ins[] = "(".$row['id_product']                      .", ".
                      $row['id_grp']                           .", ".
                      $this->txt_from_ms($row['name'],$connect).", ".
            	      $row['quantity']                         .", ".
                      $row['base_price']                       .")";
        }
        mssql_free_result($mQuery);
	$mysql_txt = "insert ignore into netbooks_tmp_products values ".implode(",",$ins);
	mysql_query($mysql_txt,$connect);

	$insertTmpPrd = microtime(true);
        $tm = $insertTmpPrd - $insertTmpGrp;
//	file_put_contents($logFile, " inserting products ".$tm, FILE_APPEND);


        $msQueryTxt = "select * from #tmp_dis_products";
        $mQuery = mssql_query($msQueryTxt, $mConnect);
        while ($row = mssql_fetch_array($mQuery,MSSQL_ASSOC)) {
            mysql_query("insert into netbooks_tmp_discounts_prods values (".$row['id_product'] .", ".
                                                                            $row['id_price'].", ".
                                                                            $row['col'].", ".
                                                                            $row['discount']   .")", $connect);
        }
        mssql_free_result($mQuery);

	$insertTmpDisp = microtime(true);
        $tm = $insertTmpDisp - $insertTmpPrd;
//	file_put_contents($logFile, " inserting disp ".$tm, FILE_APPEND);
//
	$ins = array();
        $msQueryTxt = "select * from #tmp_dis_groups";
        $mQuery = mssql_query($msQueryTxt, $mConnect);
        while ($row = mssql_fetch_array($mQuery,MSSQL_ASSOC)) {
//            mysql_query("insert into netbooks_tmp_discounts_grps values (".$row['id_group']  .", ".
//                                                                           $row['id_price'].", ".
//                                                                           $row['col']     .", ".
//                                                                           $row['discount'].")", $connect);
	    $ins[] = "(".implode(",",$row).")";
        }
        mssql_free_result($mQuery);
	$mysql_txt = "insert ignore into netbooks_tmp_discounts_grps values ".implode(",",$ins);
	mysql_query($mysql_txt,$connect);
	
	$insertTmpDisg = microtime(true);
        $tm = $insertTmpDisg - $insertTmpDisp;
//	file_put_contents($logFile, " inserting disg ".$tm, FILE_APPEND);


        mssql_close($mConnect);

	$insertTmp = microtime(true);
        $tm = $insertTmp - $getMs;
//	file_put_contents($logFile, " inserting tmp ".$tm, FILE_APPEND);
        

        
	//echo 'getting from ms time '.$tm.'; ';

///выгрузка по агентам//////////////////////////////////////////////////
        
        foreach($agentsArr as $agent){

            //агент/////////////////////////////////////////////////////

            $createTxt = "create table if not exists netbooks_".$agent."_agents (
                                    id int not null auto_increment,
                                    id_agent int not null,
                                    alias varchar(100),
                                    is_deleted bool default 0,
                                    primary key (id),
                                    unique (id_agent))";
            mysql_query($createTxt, $connect);

            $updTxt = "update netbooks_".$agent."_agents set is_deleted = 1";
            mysql_query($updTxt, $connect);            

            $insTxt = " insert into netbooks_".$agent."_agents
                            (id_agent, alias, is_deleted) 
                            select distinct *, 0 as is_deleted from netbooks_tmp_agents where id_agent = ".$agent.
                            " on duplicate key update alias = values(alias), is_deleted = 0";
            mysql_query($insTxt, $connect);

            //схема/////////////////////////////////////////////////////
            $createTxt = "create table if not exists netbooks_".$agent."_scheme (
                                    id int not null auto_increment,
                                    id_client int,
                                    id_grp smallint,
                                    is_deleted bool default 0,
                                    primary key (id),
                                    unique (id_client, id_grp))";
            mysql_query($createTxt, $connect);

            $delTxt = "update netbooks_".$agent."_scheme set is_deleted = 1";
            mysql_query($delTxt, $connect);

            $insTxt = "insert into netbooks_".$agent."_scheme (id_client, id_grp, is_deleted)
                        select distinct id_client, id_grp, 0 as is_deleted from netbooks_tmp_scheme 
                        where id_agent = ".$agent." 
                        on duplicate key update is_deleted = 0";
            mysql_query($insTxt, $connect);

            //клиенты///////////////////////////////////////////////////
            $createTxt = "create table if not exists netbooks_".$agent."_clients (
                                    id int not null auto_increment,
                                    id_client int not null,
                                    alias varchar(100),
                                    shortname varchar(100),
                                    address varchar(100),
                                    inn varchar(12),
                                    type tinyint,
                                    xcoord decimal(15,13),
                                    ycoord decimal(15,13),
                                    id_price smallint,
                                    clmn tinyint,
                                    debt decimal(14,4) default 0,
                                    is_deleted bool default 0,
                                    primary key (id),
                                    unique (id_client))";
            mysql_query($createTxt, $connect);
			
			//удалить в следующих версиях
            $alterTxt = "alter table netbooks_".$agent."_clients modify alias varchar(100)";
            mysql_query($alterTxt, $connect);
            
            $delTxt = "update netbooks_".$agent."_clients set is_deleted = 1";
            mysql_query($delTxt, $connect);

            $insTxt = "insert into netbooks_".$agent."_clients (id_client, 
                                                                alias, 
                                                                shortname,
                                                                address,
                                                                inn,
                                                                type,
                                                                xcoord,
                                                                ycoord,
                                                                id_price,
                                                                clmn,
                                                                debt,
                                                                is_deleted)
                        select distinct *, 0 as is_deleted from netbooks_tmp_clients 
                        where id_client in (select distinct id_client from 
                                                    netbooks_".$agent."_scheme where is_deleted = 0) 
                        on duplicate key update 
															alias = values(alias),
															shortname = values(shortname),
															address = values(address),
															inn = values(inn),
															type = values(type),
															xcoord = values(xcoord),
															ycoord = values(ycoord),
															id_price = values(id_price),
															clmn = values(clmn),
															debt = values(debt),
															is_deleted = 0";
            mysql_query($insTxt, $connect);

            //маршруты//////////////////////////////////////////////////
            $createTxt = "create table if not exists netbooks_".$agent."_routes (
                                    id int not null auto_increment,
                                    id_client int,
                                    visit tinyint,
                                    is_deleted bool default 0,
                                    primary key (id),
                                    unique (id_client))";
            mysql_query($createTxt, $connect);

            $delTxt = "update netbooks_".$agent."_routes set is_deleted = 1";
            mysql_query($delTxt, $connect);

            $insTxt = "insert into netbooks_".$agent."_routes (id_client, visit, is_deleted)
                        select distinct id_client, visit, 0 as is_deleted from netbooks_tmp_routes 
                        where id_agent = ".$agent." 
                        on duplicate key update 
									visit = values(visit),
									is_deleted = 0";
            mysql_query($insTxt, $connect);
            
            //группы////////////////////////////////////////////////////

            $createTxt = "create table if not exists netbooks_".$agent."_groups (
                                    id int not null auto_increment,
                                    id_grp smallint not null,
                                    name varchar(50),
                                    is_deleted bool default 0,
                                    primary key (id),
                                    unique (id_grp))";
            mysql_query($createTxt, $connect);

            $delTxt = "update netbooks_".$agent."_groups set is_deleted = 1";
            mysql_query($delTxt, $connect);

            $insTxt = "insert into netbooks_".$agent."_groups (id_grp, name, is_deleted)
                        select distinct id_grp, name, 0 as is_deleted from netbooks_tmp_groups 
                        where id_grp in (select distinct id_grp from netbooks_".$agent."_scheme) 
                        on duplicate key update 
										name = values(name),
										is_deleted = 0";
            mysql_query($insTxt, $connect);
            
            //продукты//////////////////////////////////////////////////

            $createTxt = "create table if not exists netbooks_".$agent."_products (
                                    id int not null auto_increment,
                                    id_product int not null,
                                    id_grp smallint,
                                    name varchar(100),
                                    quantity int,
                                    base_price decimal(10,4),
                                    is_deleted bool default 0,
                                    primary key (id),
                                    unique (id_product))";
            mysql_query($createTxt, $connect);
			
			//удалить в следующих версиях
            $alterTxt = "alter table netbooks_".$agent."_products modify name varchar(100)";
            mysql_query($alterTxt, $connect);
            
            $delTxt = "update netbooks_".$agent."_products set is_deleted = 1";
            mysql_query($delTxt, $connect);

            $insTxt = "insert into netbooks_".$agent."_products (id_product, 
                                                                 id_grp, 
                                                                 name, 
                                                                 quantity,
                                                                 base_price,
                                                                 is_deleted)
                        select distinct t1.*, 0 as is_deleted from netbooks_tmp_products t1 
                        where id_grp in (select distinct id_grp from netbooks_".$agent."_scheme) 
                        on duplicate key update 
													id_grp = values(id_grp),
													name = values(name),
													quantity = values(quantity),
													base_price = values(base_price),
													is_deleted = 0";
            mysql_query($insTxt, $connect);
            
            //скидки продукты///////////////////////////////////////////

            $createTxt = "create table if not exists 
                                netbooks_".$agent."_disp (
                                        id int not null auto_increment,
                                        id_product int, 
                                        id_price smallint,
                                        col tinyint,
                                        discount decimal(10,4),
                                        is_deleted bool default 0,
                                        primary key (id),
                                        unique (id_product, 
                                                id_price, 
                                                col))";
            mysql_query($createTxt, $connect);

            

            $delTxt = "update netbooks_".$agent."_disp 
                                        set is_deleted = 1";
            mysql_query($delTxt, $connect);

            $insTxt = "insert into 
                            netbooks_".$agent."_disp (id_product, 
                                                      id_price,
                                                      col,
                                                      discount,
                                                      is_deleted)
                        select distinct *, 0 as is_deleted 
                        from netbooks_tmp_discounts_prods 
                        where id_price in 
                        (select distinct id_price 
                            from netbooks_".$agent."_clients) 
                        and
                        id_product in 
                        (select distinct id_product 
                            from netbooks_".$agent."_products)
                        on duplicate key update 
												discount = values(discount),
												is_deleted = 0";
            mysql_query($insTxt, $connect);
            
            //скидки группы/////////////////////////////////////////////

            $createTxt = "create table if not exists 
                            netbooks_".$agent."_disg (
                                        id int not null auto_increment,
                                        id_grp int, 
                                        id_price smallint,
                                        col tinyint,
                                        discount decimal(10,4),
                                        is_deleted bool default 0,
                                        primary key (id),
                                        unique (id_grp, 
                                                id_price, 
                                                col))";
            mysql_query($createTxt, $connect);

            $delTxt = "update netbooks_".$agent."_disg 
                            set is_deleted = 1";
            mysql_query($delTxt, $connect);

            $insTxt = "insert into 
                            netbooks_".$agent."_disg (id_grp, 
                                                      id_price,
                                                      col,
                                                      discount,
                                                      is_deleted)
                        select distinct *, 0 as is_deleted 
                        from netbooks_tmp_discounts_grps 
                        where id_price in 
                        (select distinct id_price 
                            from netbooks_".$agent."_clients) 
                        and
                        id_grp in 
                        (select distinct id_grp 
                            from netbooks_".$agent."_groups)
                        on duplicate key update 
												discount = values(discount),
												is_deleted = 0";
            mysql_query($insTxt, $connect);
            
            //удаления//////////////////////////////////////////////////

            //$dtxt = "drop table netbooks_".$agent."_agents";
            //mysql_query($dtxt, $connect);
            
            //$dtxt = "drop table netbooks_".$agent."_scheme";
            //mysql_query($dtxt, $connect);
           
            //$dtxt = "drop table netbooks_".$agent."_clients";
            //mysql_query($dtxt, $connect);
            
            //$dtxt = "drop table netbooks_".$agent."_routes";
            //mysql_query($dtxt, $connect);
            
            //$dtxt = "drop table netbooks_".$agent."_groups";
            //mysql_query($dtxt, $connect);
            
            //$dtxt = "drop table netbooks_".$agent."_products";
            //mysql_query($dtxt, $connect);
            
            //$dtxt = "drop table netbooks_".$agent."_disp";
            //mysql_query($dtxt, $connect);
            
            //$dtxt = "drop table netbooks_".$agent."_disg";
            //mysql_query($dtxt, $connect);
            
        }

        $insAgTime = microtime(true);
        $tm = $insAgTime - $insertTmp;
//	file_put_contents($logFile, " inserting data ".$tm, FILE_APPEND);
       // echo 'inserting data '.$tm.'; ';

//агенты в терминах программы///////////////////////////////////////////
        
        $agentsMtArr = array();
        $queryTxt = "select id as i,
                            name as n,
                            id_agent as ia,
                            grp_names as g,
                            is_debt as id,
                            is_quantity as iq,
                            is_paused as ip,
                            is_deleted as d
                    from netbooks_agents where 
                                        id_license = ".$idLicense;
        $querry = mysql_query($queryTxt, $connect);
        while ($row = mysql_fetch_array($querry,MYSQL_ASSOC)){
                $agentsMtArr[] =$row ;
        }
        
        $queryTxt = "select name from netbooks_licenses 
                        where id = ".$idLicense;
        $querry = mysql_query($queryTxt, $connect);
        while ($row = mysql_fetch_array($querry,MYSQL_ASSOC)){
                $licenseName =$row['name'] ;
        }
        
//выгрузка в файл///////////////////////////////////////////////////////

        $outputArr = array();
		$outputArr['idLicense'] = $idLicense;
        $outputArr['licenseName'] = $licenseName;
        $outputArr['agentsMt'] = $agentsMtArr;
        foreach($agentsArr as $agent){
            $agentArr = array();
            $agentArr['idagent']=$agent;
            
            $queryTxt = "select id as i, 
                                alias as a, 
                                is_deleted as d 
                            from netbooks_".$agent."_agents";
            $querry = mysql_query($queryTxt, $connect);
            while ($row = mysql_fetch_array($querry,MYSQL_ASSOC)){
                $agentArr['agent'][] =$row ;
            }

            $queryTxt = "select id as i, 
                                id_client as ic, 
                                id_grp as ig, 
                                is_deleted as d 
                            from netbooks_".$agent."_scheme";
            $querry = mysql_query($queryTxt, $connect);
            while ($row = mysql_fetch_array($querry,MYSQL_ASSOC)){
                $agentArr['scheme'][] =$row;
            }

            $queryTxt = "select id as i, 
                                id_client as ic, 
                                alias as a, 
                                address as ad, 
                                id_price as ap, 
                                clmn as c, 
                                debt as db, 
                                is_deleted as d 
                            from netbooks_".$agent."_clients";
            $querry = mysql_query($queryTxt, $connect);
            while ($row = mysql_fetch_array($querry,MYSQL_ASSOC)){
                $agentArr['clients'][] =$row;
            }

            $queryTxt = "select id as i, 
                                id_client as ic, 
                                visit as v, 
                                is_deleted as d 
                            from netbooks_".$agent."_routes";
            $querry = mysql_query($queryTxt, $connect);
            while ($row = mysql_fetch_array($querry,MYSQL_ASSOC)){
                $agentArr['routes'][] =$row;
            }

            $queryTxt = "select id as i, 
                                id_grp as ig, 
                                name as n, 
                                is_deleted as d 
                            from netbooks_".$agent."_groups";
            $querry = mysql_query($queryTxt, $connect);
            while ($row = mysql_fetch_array($querry,MYSQL_ASSOC)){
                $agentArr['groups'][] =$row;
            }

            $queryTxt = "select id as i, 
                                id_product as ip, 
                                id_grp as ig, 
                                name as n, 
                                quantity as q, 
                                base_price as bp, 
                                is_deleted as d 
                            from netbooks_".$agent."_products";
            $querry = mysql_query($queryTxt, $connect);
            while ($row = mysql_fetch_array($querry,MYSQL_ASSOC)){
                $agentArr['products'][] =$row;
            }

            $queryTxt = "select id as i, 
                                id_product as ip, 
                                id_price as ipr, 
                                col as c, 
                                discount as ds, 
                                is_deleted as d 
                            from netbooks_".$agent."_disp";
            $querry = mysql_query($queryTxt, $connect);
            while ($row = mysql_fetch_array($querry,MYSQL_ASSOC)){
                $agentArr['disp'][] =$row;
            }

            $queryTxt = "select id as i, 
                                id_grp as ig, 
                                id_price as ipr, 
                                col as c, 
                                discount as ds, 
                                is_deleted as d 
                            from netbooks_".$agent."_disg";
            $querry = mysql_query($queryTxt, $connect);
            while ($row = mysql_fetch_array($querry,MYSQL_ASSOC)){
                $agentArr['disg'][] =$row;
            }

            $outputArr['agents'][] = $agentArr;
        }

        mysql_close($connect);
        
        $fileName = time().rand(0, 10000);
        $fn = $pth.'/'.$fileName.'.json';
        $content = json_encode($outputArr);
        $f = fopen($fn,"w");
        fclose($f);
        file_put_contents($fn,$content);
        
        $zip = new ZipArchive();
        $zip->open($fn.'.zip', ZIPARCHIVE::CREATE);
        $zip->addFile($fn, $fileName.'.json');
        $zip->close();
        
        //unlink($fn);
        
        $saveTime = microtime(true);
        $tm = $saveTime - $insAgTime;
//	file_put_contents($logFile, " saving in file ".$tm, FILE_APPEND);
       // echo 'saving data '.$tm.'; ';
        

        $end = microtime(true);
        $tm = $end - $start;
       // echo 'total '.$tm.'; ';
        
        return $fileName;
    }
}
?>
