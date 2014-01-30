<?php
/**
 * client_agents.model.php, 
 * mt-1.0.1,
 * client
 * 
 * модель представляющая все таблицы модуля
 * netbooks
 * 
 */
class Client_Agents_Model extends Db{
    function __construct($dbConfArr){
        $this->set_db_Conf($dbConfArr);
        $this->create_tables();        
    }
    
    function create_tables(){
        $createTxt = "create table if not exists client_agents_mt (
                        id smallint not null,
                        alias varchar(50),
                        id_agent int,
                        grp_names varchar(100),
                        is_debt bool,
                        is_quantity bool,
                        is_paused bool,
                        is_deleted bool,
                        primary key (id))";
        $this->my_query($createTxt);
        
        $createTxt = "create table if not exists client_license (
                            id smallint not null,
                            alias varchar(50) not null,
                            last_sync_date date default '1901-01-01',
                            last_sync_time time default '00:00:00',
                            primary key (id))";
        $this->my_query($createTxt);
        
        $createTxt = "create table if not exists client_params (
                            param varchar(20) not null,
                            val varchar(50),
                            primary key (param))";
        $this->my_query($createTxt);
        
        $createTxt = "create table if not exists client_orders (
                            id int not null auto_increment,
                            id_amt smallint,
                            id_agent int,
                            id_client int,
                            dt date,
                            tm time,
                            d_date date,
                            total decimal(10,4),
                            comment varchar(200),
                            is_deleted bool default 0,
                            is_accepted bool default 0,
                            is_sended bool default 0,
                            primary key (id))";
        $this->my_query($createTxt);
        
        $createTxt = "create table if not exists 
                        client_orders_details (
                            id int not null auto_increment,
                            id_order int,
                            id_product int,
                            amount int,
                            price decimal(10,4),
                            total decimal(10,4),
                            is_deleted bool default 0,
                            primary key (id),
                            unique (id_order, id_product))";
        $this->my_query($createTxt);
    }
    
    function insert_updates($contentArr){
        //агенты MT=====================================================
        $agentsMtArr = isset($contentArr['agentsMt'])? 
                                            $contentArr['agentsMt']: 0;
        if(is_array($agentsMtArr)){
            $valsArr = array();
            foreach($agentsMtArr as $row){
                $val = "(".$row['i'].", '".
                        $row['n']."', ".
                        $row['ia'].", '".
                        $row['g']."', ".
                        $row['id'].", ".
                        $row['iq'].", ".
                        $row['ip'].", ".
                        $row['d'].")";
                $valsArr[] = $val;
            }
            $vals = implode(",",$valsArr);
        
            $updateTxt = "update client_agents_mt set is_deleted = 1";
            $this->my_query($updateTxt);
        
            $insertTxt = "insert into client_agents_mt values ".$vals."
                            on duplicate key update 
                                id          = values(id),
                                alias       = values(alias),
                                id_agent    = values(id_agent),
                                grp_names   = values(grp_names),
                                is_debt     = values(is_debt),
                                is_quantity = values(is_quantity),
                                is_paused   = values(is_paused),
                                is_deleted  = values(is_deleted)";
            $this->my_query($insertTxt);
        }
        //данные========================================================
        
        $agentsArr = isset($contentArr['agents'])? 
                                            $contentArr['agents']: 0;
        if(is_array($agentsArr)){
            foreach($agentsArr as $agent){
                $idAgent   = $agent['idagent'];
                $agentName = $agent['agent'][0]['a'];
                
                $updateTxt = "update client_agents_mt 
                    set alias = '".$agentName."' 
                    where id_agent = ".$idAgent;
                $this->my_query($updateTxt);
                
                //схема=================================================
                $createTxt = "create table if not exists 
                                client_".$idAgent."_scheme (
                                    id int not null,
                                    id_client int not null,
                                    id_grp smallint not null,
                                    is_deleted bool default 0,
                                    primary key (id))";
                $this->my_query($createTxt);
                
                $schemeArr = isset($agent['scheme'])? 
                                                $agent['scheme']: 0;
                if(is_array($schemeArr)){
                    $valsArr = array();
                    foreach($schemeArr as $row){
                        $val = "(".$row['i'].", ".
                                   $row['ic'].", ".
                                   $row['ig'].", ".
                                   $row['d'].")";
                        $valsArr[] = $val;
                    }
                    $vals = implode(",",$valsArr);
                    
                    $updateTxt = "update client_".$idAgent."_scheme 
                                                    set is_deleted = 1";
                    $this->my_query($updateTxt);
                    
                    $insTxt = "insert into client_".$idAgent."_scheme 
                                values ".$vals." 
                                on duplicate key update
                                    id         = values(id),
                                    id_client  = values(id_client),
                                    id_grp     = values(id_grp),
                                    is_deleted = values(is_deleted)";
                    $this->my_query($insTxt);
                }
                //клиенты===============================================
                $createTxt = "create table if not exists 
                                client_".$idAgent."_clients (
                                    id int not null,
                                    id_client int not null,
                                    alias varchar(100),
                                    address varchar(100),
                                    id_price int,
                                    clmn tinyint,
                                    debt decimal(14,4),
                                    is_deleted bool default 0,
                                    primary key (id))";
                $this->my_query($createTxt);
                
                //удалить в следующих версиях
                $alterTxt = "alter table client_".$idAgent."_clients modify alias varchar(100)";
                $this->my_query($alterTxt);
                
                $clientsArr = isset($agent['clients'])? 
                                                   $agent['clients']: 0;
                $m_connect = mysql_connect($this->host, $this->user, $this->password);
                if(is_array($clientsArr)){
                    $valsArr = array();
                    foreach($clientsArr as $row){
                        $val = "(".$row['i'].", ".
                                   $row['ic'].", '".
                                   mysql_real_escape_string($row['a'],$m_connect)."', '".
                                   mysql_real_escape_string($row['ad'],$m_connect)."', ".
                                   $row['ap'].", ".
                                   $row['c'].", ".
                                   $row['db'].", ".
                                   $row['d'].")";
                        $valsArr[] = $val;
                    }
               mysql_close($m_connect);
                    $vals = implode(",",$valsArr);
                    $updateTxt = "update client_".$idAgent."_clients 
                                                    set is_deleted = 1";
                    $this->my_query($updateTxt);
                    
                    $insTxt = "insert into client_".$idAgent."_clients 
                                values ".$vals." 
                                on duplicate key update
                                    id         = values(id),
                                    id_client  = values(id_client),
                                    alias      = values(alias),
                                    address    = values(address),
                                    id_price   = values(id_price),
                                    clmn       = values(clmn),
                                    debt       = values(debt),
                                    is_deleted = values(is_deleted)";
                    $this->my_query($insTxt);
                }
                //маршруты==============================================
                $createTxt = "create table if not exists 
                                client_".$idAgent."_routes (
                                    id int not null,
                                    id_client int not null,
                                    visit tinyint,
                                    is_deleted bool default 0,
                                    primary key (id))";
                $this->my_query($createTxt);
                $routesArr = isset($agent['routes'])? 
                                                    $agent['routes']: 0;
                if(is_array($routesArr)){
                    $valsArr = array();
                    foreach($routesArr as $row){
                        $val = "(".$row['i'].", ".
                                   $row['ic'].", ".
                                   $row['v'].", ".
                                   $row['d'].")";
                        $valsArr[] = $val;
                    }
                    $vals = implode(",",$valsArr);
                    
                    $updateTxt = "update client_".$idAgent."_routes 
                                                    set is_deleted = 1";
                    $this->my_query($updateTxt);
                    
                    $insTxt = "insert into client_".$idAgent."_routes 
                                values ".$vals." 
                                on duplicate key update
                                    id         = values(id),
                                    id_client  = values(id_client),
                                    visit      = values(visit),
                                    is_deleted = values(is_deleted)";
                    $this->my_query($insTxt);
                }
                //группы================================================
                $createTxt = "create table if not exists 
                                client_".$idAgent."_groups (
                                    id int not null,
                                    id_grp smallint not null,
                                    alias varchar(50),
                                    is_deleted bool default 0,
                                    primary key (id))";
                $this->my_query($createTxt);
                $groupsArr = isset($agent['groups'])? 
                                                    $agent['groups']: 0;
                if(is_array($groupsArr)){
                    $valsArr = array();
                    foreach($groupsArr as $row){
                        $val = "(".$row['i'].", ".
                                   $row['ig'].", '".
                                   $row['n']."', ".
                                   $row['d'].")";
                        $valsArr[] = $val;
                    }
                    $vals = implode(",",$valsArr);
                    
                    $updateTxt = "update client_".$idAgent."_groups 
                                                    set is_deleted = 1";
                    $this->my_query($updateTxt);
                    
                    $insTxt = "insert into client_".$idAgent."_groups 
                                values ".$vals." 
                                on duplicate key update
                                    id         = values(id),
                                    id_grp     = values(id_grp),
                                    alias      = values(alias),
                                    is_deleted = values(is_deleted)";
                    $this->my_query($insTxt);
                }
                //продукты==============================================
                
                
                
                $createTxt = "create table if not exists 
                                client_".$idAgent."_products (
                                    id int not null,
                                    id_product int not null,
                                    id_grp smallint,
                                    alias varchar(100),
                                    quantity int,
                                    base_price decimal(10,4),
                                    is_deleted bool default 0,
                                    primary key (id))";
                $this->my_query($createTxt);
                
                //удалить в следующих версиях
                $alterTxt = "alter table client_".$idAgent."_products modify alias varchar(100)";
                $this->my_query($alterTxt);
                
                $productsArr = isset($agent['products'])? 
                                                    $agent['products']: 0;
                if(is_array($productsArr)){
                    $valsArr = array();
                    foreach($productsArr as $row){
                        $val = "(".$row['i'].", ".
                                   $row['ip'].", ".
                                   $row['ig'].", '".
                                   $row['n']."', ".
                                   $row['q'].", ".
                                   $row['bp'].", ".
                                   $row['d'].")";
                        $valsArr[] = $val;
                    }
                    $vals = implode(",",$valsArr);
                    
                    $updateTxt = "update client_".$idAgent."_products 
                                                    set is_deleted = 1";
                    $this->my_query($updateTxt);
                    
                    $insTxt = "insert into client_".$idAgent."_products 
                                values ".$vals." 
                                on duplicate key update
                                    id         = values(id),
                                    id_product = values(id_product),
                                    id_grp     = values(id_grp),
                                    alias      = values(alias),
                                    quantity   = values(quantity),
                                    base_price = values(base_price),
                                    is_deleted = values(is_deleted)";
                    $lf = "/var/www/mt/product/mt-1.0.1/modules/client/exchange/in/log.txt";
                    file_put_contents($lf, $insTxt);
                    $this->my_query($insTxt);
                }
                //скидки продукты=======================================
                $createTxt = "create table if not exists 
                                client_".$idAgent."_disp (
                                    id int not null,
                                    id_product int not null,
                                    id_price int not null,
                                    clmn tinyint,
                                    discount decimal(10,4),
                                    is_deleted bool default 0,
                                    primary key (id))";
                $this->my_query($createTxt);
                $dispArr = isset($agent['disp'])? 
                                                    $agent['disp']: 0;
                if(is_array($dispArr)){
                    $valsArr = array();
                    foreach($dispArr as $row){
                        $val = "(".$row['i'].", ".
                                   $row['ip'].", ".
                                   $row['ipr'].", ".
                                   $row['c'].", ".
                                   $row['ds'].", ".
                                   $row['d'].")";
                        $valsArr[] = $val;
                    }
                    $vals = implode(",",$valsArr);
                    
                    $updateTxt = "update client_".$idAgent."_disp 
                                                    set is_deleted = 1";
                    $this->my_query($updateTxt);
                    
                    $insTxt = "insert into client_".$idAgent."_disp 
                                values ".$vals." 
                                on duplicate key update
                                    id         = values(id),
                                    id_product = values(id_product),
                                    id_price   = values(id_price),
                                    clmn       = values(clmn),
                                    discount   = values(discount),
                                    is_deleted = values(is_deleted)";
                    $this->my_query($insTxt);
                }
                //скидки группы=======================================
                $createTxt = "create table if not exists 
                                client_".$idAgent."_disg (
                                    id int not null,
                                    id_grp smallint not null,
                                    id_price int not null,
                                    clmn tinyint,
                                    discount decimal(10,4),
                                    is_deleted bool default 0,
                                    primary key (id))";
                $this->my_query($createTxt);
                $disgArr = isset($agent['disg'])? 
                                                    $agent['disg']: 0;
                if(is_array($disgArr)){
                    $valsArr = array();
                    foreach($disgArr as $row){
                        $val = "(".$row['i'].", ".
                                   $row['ig'].", ".
                                   $row['ipr'].", ".
                                   $row['c'].", ".
                                   $row['ds'].", ".
                                   $row['d'].")";
                        $valsArr[] = $val;
                    }
                    $vals = implode(",",$valsArr);
                    
                    $updateTxt = "update client_".$idAgent."_disg 
                                                    set is_deleted = 1";
                    $this->my_query($updateTxt);
                    
                    $insTxt = "insert into client_".$idAgent."_disg 
                                values ".$vals." 
                                on duplicate key update
                                    id         = values(id),
                                    id_grp     = values(id_grp),
                                    id_price   = values(id_price),
                                    clmn       = values(clmn),
                                    discount   = values(discount),
                                    is_deleted = values(is_deleted)";
                    $this->my_query($insTxt);
                }
            } 
        }
    }
   
    function get_price_list($idAgent, $idClient){
        $queryTxt = "select 
                        tt.id_product, 
                        tt.grp_name, 
                        tt.alias, 
                        round( if(tt.disp = 0, 
                            (tt.base_price-(tt.base_price/100)*tt.disg),
                            (tt.base_price-(tt.base_price/100)*tt.disp)
                        ), 2) as price
                    from     
                        (select 
                            tA.*, 
                            if(tB.discount is not null, 
                                tB.discount, 
                                0) as disg,
                            if(tC.discount is not null, 
                                tC.discount, 
                                0) as disp 
                        from 
                            (select 
                                t1.id_product, 
                                t1.id_grp, 
                                t2.alias as grp_name, 
                                t1.alias, 
                                t1.base_price 
                            from
                                client_".$idAgent."_products t1 
                            left join 
                                client_".$idAgent."_groups t2
                            on 
                                t1.id_grp = t2.id_grp 
                            where 
                                t1.is_deleted = 0) tA
                        left join 
                            (select 
                                id_grp, 
                                discount 
                            from 
                                client_".$idAgent."_disg 
                            where 
                                id_price = (select distinct 
                                                id_price 
                                            from  
                                                client_".$idAgent."_clients 
                                            where 
                                                id_client = ".$idClient." 
                                            and 
                                                is_deleted = 0) 
                            and
                                clmn = (select distinct 
                                            clmn 
                                        from  
                                            client_".$idAgent."_clients 
                                        where 
                                            id_client = ".$idClient." 
                                        and 
                                            is_deleted = 0)
                            and 
								is_deleted = 0) tB
                        on 
                            tA.id_grp = tB.id_grp
                        left join
                            (select 
                                id_product, 
                                discount 
                            from 
                                client_".$idAgent."_disp 
                            where 
                                id_price = (select distinct 
                                                id_price 
                                            from  
                                                client_".$idAgent."_clients 
                                            where 
                                                id_client = ".$idClient." 
                                            and 
                                                is_deleted = 0) 
                            and
                                clmn = (select distinct 
                                            clmn 
                                        from 
                                            client_".$idAgent."_clients 
                                        where 
                                            id_client = ".$idClient." 
                                        and 
                                            is_deleted = 0)
                            and 
								is_deleted = 0) tC
                        on 
                            tA.id_product = tC.id_product) tt";
        $ret = $this->my_query($queryTxt,'JSON');
        return $ret;
    }
    
    function get_clients(){
        $queryTxt = "select distinct 
                        id,
                        id_agent, 
                        alias, 
                        grp_names, 
                        is_debt 
                    from 
                        client_agents_mt 
                    where 
                        is_deleted = 0 
                    and 
                        is_paused = 0";
        $agentsArr = $this->my_query($queryTxt,'ARR');
        $arr = array();
        foreach($agentsArr as $agentsArr){
            $id      = $agentsArr['id'];
            $idAgent = $agentsArr['id_agent'];
            $isDebt  = $agentsArr['is_debt'];
            $queryTxt = "select
                            ".$id." as ida,
                            tt.id_agent, 
                            tt.id_client, 
                            tt.alias, 
                            tt.address, 
                            tt.dbt,
                            group_concat(tt.day order by tt.n separator ' ') as days,
                            min(tt.n) as ord
                        from
                            (
                                (select 
                                    ".$idAgent." as id_agent, 
                                    t1.id_client,
                                    t1.alias,
                                    t1.address,
                                    if(".$isDebt." = 1, 
                                        if(t1.debt = 1,'да','нет'), 
                                        '--') as dbt,
                                    '--' as day,
                                    0 as n
                                from 
                                    client_".$idAgent."_clients t1
                                left join
                                    client_".$idAgent."_routes t2
                                on
                                    t1.id_client = t2.id_client
                                where 
                                    t1.is_deleted = 0
                                and
                                    t2.visit is null)
                            union
                                (select 
                                    ".$idAgent." as id_agent, 
                                    t1.id_client,
                                    t1.alias,
                                    t1.address,
                                    if(".$isDebt." = 1, 
                                        if(t1.debt = 1,'да','нет'), 
                                        '--') as dbt,
                                    'пнд' as day,
                                    1 as n
                                from 
                                    client_".$idAgent."_clients t1
                                left join
                                    client_".$idAgent."_routes t2
                                on
                                    t1.id_client = t2.id_client
                                where 
                                    t1.is_deleted = 0
                                and
                                    (t2.visit & 1) = 1)
                            union
                                (select 
                                    ".$idAgent." as id_agent, 
                                    t1.id_client,
                                    t1.alias,
                                    t1.address,
                                    if(".$isDebt." = 1, 
                                        if(t1.debt = 1,'да','нет'), 
                                        '--') as dbt,
                                    'вт' as day,
                                    2 as n
                                from 
                                    client_".$idAgent."_clients t1
                                left join
                                    client_".$idAgent."_routes t2
                                on
                                    t1.id_client = t2.id_client
                                where 
                                    t1.is_deleted = 0
                                and
                                    (t2.visit & 2) = 2)
                            union
                                (select 
                                    ".$idAgent." as id_agent, 
                                    t1.id_client,
                                    t1.alias,
                                    t1.address,
                                    if(".$isDebt." = 1, 
                                        if(t1.debt = 1,'да','нет'), 
                                        '--') as dbt,
                                    'ср' as day,
                                    3 as n
                                from 
                                    client_".$idAgent."_clients t1
                                left join
                                    client_".$idAgent."_routes t2
                                on
                                    t1.id_client = t2.id_client
                                where 
                                    t1.is_deleted = 0
                                and
                                    (t2.visit & 4) = 4)
                            union
                                (select 
                                    ".$idAgent." as id_agent, 
                                    t1.id_client,
                                    t1.alias,
                                    t1.address,
                                    if(".$isDebt." = 1, 
                                        if(t1.debt = 1,'да','нет'), 
                                        '--') as dbt,
                                    'чт' as day,
                                    4 as n
                                from 
                                    client_".$idAgent."_clients t1
                                left join
                                    client_".$idAgent."_routes t2
                                on
                                    t1.id_client = t2.id_client
                                where 
                                    t1.is_deleted = 0
                                and
                                    (t2.visit & 8) = 8)
                            union
                                (select 
                                    ".$idAgent." as id_agent, 
                                    t1.id_client,
                                    t1.alias,
                                    t1.address,
                                    if(".$isDebt." = 1, 
                                        if(t1.debt = 1,'да','нет'), 
                                        '--') as dbt,
                                    'пт' as day,
                                    5 as n
                                from 
                                    client_".$idAgent."_clients t1
                                left join
                                    client_".$idAgent."_routes t2
                                on
                                    t1.id_client = t2.id_client
                                where 
                                    t1.is_deleted = 0
                                and
                                    (t2.visit & 16) = 16)
                            union
                                (select 
                                    ".$idAgent." as id_agent, 
                                    t1.id_client,
                                    t1.alias,
                                    t1.address,
                                    if(".$isDebt." = 1, 
                                        if(t1.debt = 1,'да','нет'), 
                                        '--') as dbt,
                                    'сб' as day,
                                    6 as n
                                from 
                                    client_".$idAgent."_clients t1
                                left join
                                    client_".$idAgent."_routes t2
                                on
                                    t1.id_client = t2.id_client
                                where 
                                    t1.is_deleted = 0
                                and
                                    (t2.visit & 32) = 32)
                            union
                                (select 
                                    ".$idAgent." as id_agent, 
                                    t1.id_client,
                                    t1.alias,
                                    t1.address,
                                    if(".$isDebt." = 1, 
                                        if(t1.debt = 1,'да','нет'), 
                                        '--') as dbt,
                                    'вс' as day,
                                    7 as n
                                from 
                                    client_".$idAgent."_clients t1
                                left join
                                    client_".$idAgent."_routes t2
                                on
                                    t1.id_client = t2.id_client
                                where 
                                    t1.is_deleted = 0
                                and
                                    (t2.visit & 64) = 64)
                            ) tt
                        group by 
                            tt.id_client
                        order by tt.n, tt.alias";
                            
                            
            $arr = array_merge($arr, $this->my_query($queryTxt,'ARR'));
        }
        return $ret = "{\"rows\":".json_encode($arr).'}';
    
    }
   
    function get_agents(){
        
        $connect = mysql_connect($this->host, $this->user, $this->password);
        mysql_select_db($this->dbName, $connect);
        
        $queryTxt = "select id_agent, alias from client_agents_mt
                        where is_deleted = 0 and is_paused = 0";
        $query = mysql_query($queryTxt, $connect);
        
        $arr = array();
        $n = 1;
        while ($row = mysql_fetch_array($query,MYSQL_ASSOC)){
            $arrR = array();
            $arrR['n'] = $n;
            $arrR['id_agent'] =  $row['id_agent'];
            $arrR['alias'] =  $row['alias'];
            
            $arr[] = $arrR;
            $n = $n + 1;
        }
        
        $ret = "{\"rows\":".json_encode($arr).'}';
        return $ret;
    }
 
    function get_new_order($idA, $idClient){
        $queryTxt = "select distinct 
                        id,
                        id_agent, 
                        is_quantity 
                    from 
                        client_agents_mt
                    where
                        id = ".$idA; 
        $agentsArr = $this->my_query($queryTxt, 'ARR');
        if(is_array($agentsArr)){
            $idAmt = $agentsArr[0]['id'];
            $idAgent = $agentsArr[0]['id_agent'];
            $isQuantity = $agentsArr[0]['is_quantity'];
        }else{
            exit;
        } 
        
        $queryTxt = "select t.* from
					(select 
						id 
					from 
						client_orders 
					 where 
                            id_client = ".$idClient." 
                        and
                            id_amt = ".$idAmt." 
                        and
                            is_deleted = 0
					order by id desc
					limit 4) t
					order by t.id desc";
		$arr = $this->my_query($queryTxt, 'ARR');
		
		$q1 = "0 as order1,
               0 as order2,
               0 as order3,
               0 as order4,";
        $q2 = "";
        
        if(is_array($arr)){
			if(isset($arr[0]['id'])){
				$id1 = $arr[0]['id'];
				$q1 = "ifnull(tt1.amount, 0) as order1,
					   0 as order2,
					   0 as order3,
                       0 as order4,";
                
                $q2 = "left join (select id_product, amount 
						from client_orders_details where id_order = ".$id1." 
						and is_deleted = 0) tt1 on tt.id_product = tt1.id_product";
			}
			if(isset($arr[1]['id'])){
				$id2 = $arr[1]['id'];
				$q1 = "ifnull(tt1.amount, 0) as order1,
					   ifnull(tt2.amount, 0) as order2,
					   0 as order3,
                       0 as order4,";
                
                $q2 = "left join (select id_product, amount 
						from client_orders_details where id_order = ".$id1." 
						and is_deleted = 0) tt1 on tt.id_product = tt1.id_product
						
						left join (select id_product, amount 
						from client_orders_details where id_order = ".$id2." 
						and is_deleted = 0) tt2 on tt.id_product = tt2.id_product";
			}
			if(isset($arr[2]['id'])){
				$id3 = $arr[2]['id'];
				$q1 = "ifnull(tt1.amount, 0) as order1,
					   ifnull(tt2.amount, 0) as order2,
					   ifnull(tt3.amount, 0) as order3,
                       0 as order4,";
                
                $q2 = "left join (select id_product, amount 
						from client_orders_details where id_order = ".$id1." 
						and is_deleted = 0) tt1 on tt.id_product = tt1.id_product
						
						left join (select id_product, amount 
						from client_orders_details where id_order = ".$id2." 
						and is_deleted = 0) tt2 on tt.id_product = tt2.id_product
						
						left join (select id_product, amount 
						from client_orders_details where id_order = ".$id3." 
						and is_deleted = 0) tt3 on tt.id_product = tt3.id_product";
			}
			if(isset($arr[3]['id'])){
				$id4 = $arr[3]['id'];
				$q1 = "ifnull(tt1.amount, 0) as order1,
					   ifnull(tt2.amount, 0) as order2,
					   ifnull(tt3.amount, 0) as order3,
                       ifnull(tt4.amount, 0) as order4,";
                
                $q2 = "left join (select id_product, amount 
						from client_orders_details where id_order = ".$id1." 
						and is_deleted = 0) tt1 on tt.id_product = tt1.id_product
						
						left join (select id_product, amount 
						from client_orders_details where id_order = ".$id2." 
						and is_deleted = 0) tt2 on tt.id_product = tt2.id_product
						
						left join (select id_product, amount 
						from client_orders_details where id_order = ".$id3." 
						and is_deleted = 0) tt3 on tt.id_product = tt3.id_product
						
						left join (select id_product, amount 
						from client_orders_details where id_order = ".$id4." 
						and is_deleted = 0) tt4 on tt.id_product = tt4.id_product";
			}
		
		}
        
        $queryTxt = "select 
                        tt.id_product, 
                        tt.grp_name, 
                        tt.alias, 
                        
                       ".$q1."
                        
                        round( if(tt.disp = 0, 
                            (tt.base_price-(tt.base_price/100)*tt.disg),
                            (tt.base_price-(tt.base_price/100)*tt.disp)
                        ), 2) as price,
                        
                        if(".$isQuantity." = 1, 
                            tt.quantity, 
                            0) as quantity,
                        0 as amount,
                        0 as total
                        
                    from     
                        (select 
                            tA.*, 
                            if(tB.discount is not null, 
                                tB.discount, 
                                0) as disg,
                            if(tC.discount is not null, 
                                tC.discount, 
                                0) as disp 
                        from 
                            (select 
                                t1.id_product, 
                                t1.id_grp, 
                                t2.alias as grp_name, 
                                t1.alias, 
                                t1.base_price,
                                t1.quantity 
                            from
                                client_".$idAgent."_products t1 
                            left join 
                                client_".$idAgent."_groups t2
                            on 
                                t1.id_grp = t2.id_grp 
                            where 
                                t1.is_deleted = 0) tA
                        left join 
                            (select 
                                id_grp, 
                                discount 
                            from 
                                client_".$idAgent."_disg 
                            where 
                                id_price = (select distinct 
                                                id_price 
                                            from  
                                                client_".$idAgent."_clients 
                                            where 
                                                id_client = ".$idClient." 
                                            and 
                                                is_deleted = 0) 
                            and
                                clmn = (select distinct 
                                            clmn 
                                        from  
                                            client_".$idAgent."_clients 
                                        where 
                                            id_client = ".$idClient." 
                                        and 
                                            is_deleted = 0)
                            and 
								is_deleted = 0) tB
                        on 
                            tA.id_grp = tB.id_grp
                        left join
                            (select 
                                id_product, 
                                discount 
                            from 
                                client_".$idAgent."_disp 
                            where 
                                id_price = (select distinct 
                                                id_price 
                                            from  
                                                client_".$idAgent."_clients 
                                            where 
                                                id_client = ".$idClient." 
                                            and 
                                                is_deleted = 0) 
                            and
                                clmn = (select distinct 
                                            clmn 
                                        from 
                                            client_".$idAgent."_clients 
                                        where 
                                            id_client = ".$idClient." 
                                        and 
                                            is_deleted = 0)
                            and 
								is_deleted = 0) tC
                        on 
                            tA.id_product = tC.id_product) tt ".$q2." 
                        order by
                            tt.grp_name, tt.alias";
        $ret = $this->my_query($queryTxt,'JSON');
        //print_r($this->my_query($queryTxt,'ARR'));
        return $ret;
    }
    
    function save_new_order($inputArr){
        $total     = $inputArr[0];
        $idAmt     = $inputArr[1];
        $idClient  = $inputArr[2];
        $rows      = $inputArr[3];
        $delivDate = $inputArr[4];
        $comment   = $inputArr[5];
        
        $connect = mysql_connect($this->host, $this->user, 
                                                    $this->password);
        mysql_select_db($this->dbName, $connect);
        
        $queryTxt = "select id_agent from client_agents_mt 
                     where id = ".$idAmt;
        $query = mysql_query($queryTxt, $connect);
        
         while ($row = mysql_fetch_array($query,MYSQL_ASSOC)){
            $arr[] =  $row['id_agent'];
        }
        
        if(!(is_array($arr))){
			
			exit;
		}
        $idAgent = $arr[0];
        
        $insTxt = "insert into client_orders (id_amt, id_agent, 
                                            id_client, dt, tm, total, d_date, comment)
				   values (".$idAmt.", ".$idAgent.", ".$idClient.", 
                            date(now()), time(now()), ".$total.", '".$delivDate."', '".$comment."')";
		
		$query = mysql_query($insTxt, $connect);
		
		$id = mysql_insert_id($connect);
		
		foreach ($rows as $value){
            $id_product = $value[0];
            $amount = $value[1];
            $price = $value[2];
            $total = $value[3];
            if ($amount>0){
                $insTxt = "insert into client_orders_details 
                           (id_order, id_product, amount, price, total) 
                            values (".$id.", 
                                    ".$id_product.", 
                                    ".$amount.",
                                    ".$price.",
                                    ".$total.")";
                mysql_query($insTxt, $connect);
            }
        }
        
        mysql_close($connect);
        
        $result['idorder'] = $id;
        $ret = json_encode($result);
        return $ret;
    
    }
	
    function save_order($inputArr){
        $total = $inputArr[0];
        $idOrder = $inputArr[1];
        $rows = $inputArr[2];
        $delivDate = $inputArr[3];
        $comment   = $inputArr[4];
        
    
        $updTxt = "update client_orders set dt      = date(now()), 
                                            tm      = time(now()),
                                            total   = ".$total.",
                                            d_date  = '".$delivDate."',
                                            comment = '".$comment."'
                    where id = ".$idOrder;
		$this->my_query($updTxt);
		
		foreach ($rows as $value){
            $id_product = $value[0];
            $amount = $value[1];
            $price = $value[2];
            $total = $value[3];
            $insTxt = "insert into client_orders_details 
                        (id_order, id_product, amount, price, total) 
                        values (".$idOrder.", ".
                                  $id_product.", ".
                                  $amount.", ".
                                  $price.", ".
                                  $total.")
                        on duplicate key update
                            amount = ".$amount.",
                            price  = ".$price.",
                            total  = ".$total;
            //echo $insTxt;
           $this->my_query($insTxt);  
        }
        
        $updTxt = "update client_orders_details set is_deleted = 1 where
                    amount = 0";
        $this->my_query($updTxt);
        
        $updTxt = "update client_orders_details set is_deleted = 0 where
                    amount > 0";
        $this->my_query($updTxt);
        
        $queryTxt = "select count(id) as c from client_orders_details
                        where id_order = ".$idOrder." 
                        and is_deleted = 0";
        $arr = $this->my_query($queryTxt,'ARR');
        if($arr[0]['c'] == 0){
            $updTxt = "update client_orders set is_deleted = 1 
                        where id = ".$idOrder;
            $this->my_query($updTxt);
            $idOrder = 'Новый заказ';
        }
        
        $result['idorder'] = $idOrder;
        $ret = json_encode($result);
        return $ret;
    
    }
	
	function get_order($idOrder){
		$queryTxt = "select id_amt, id_agent, id_client, is_accepted, is_sended, dt, tm from client_orders where id = ".$idOrder;

		$arr = $this->my_query($queryTxt, 'ARR');
		if(!is_array($arr)){
			exit;
		}
		$idAgent    = $arr[0]['id_agent'];
		$idClient   = $arr[0]['id_client'];
		$idAmt      = $arr[0]['id_amt'];
        $isAccepted = $arr[0]['is_accepted'];
		$isSended   = $arr[0]['is_sended'];
        $dt         = $arr[0]['dt'];
        $tm         = $arr[0]['tm'];
        
        
        if(($isAccepted + $isSended) == 0){
            $queryTxt = "select is_quantity from client_agents_mt where id = ".$idAmt;
            $arr = $this->my_query($queryTxt, 'ARR');
            if(!is_array($arr)){
                exit;
            }
            $isQuantity = $arr[0]['is_quantity'];
            
            $queryTxt = "select t.* from
                        (select 
                            id 
                        from 
                            client_orders 
                        where 
                            id_client = ".$idClient." 
                        and
                            id_amt = ".$idAmt." 
                        and
                            is_deleted = 0
                        and 
                            id <> ".$idOrder."
                        and 
                            dt <= '".$dt."'
                        and 
                            tm < '".$tm."'
                        order by id desc
                        limit 4) t
                        order by t.id desc";
            $arr = $this->my_query($queryTxt, 'ARR');
            
            $q1 = "0 as order1,
                   0 as order2,
                   0 as order3,
                   0 as order4,";
             $q2 = "";
            
            if(is_array($arr)){
                
                if(isset($arr[0]['id'])){
                    $id1 = $arr[0]['id'];
                    $q1 = "ifnull(tt1.amount, 0) as order1,
                           0 as order2,
                           0 as order3,
                           0 as order4,";
                    
                    $q2 = "left join (select id_product, amount 
                            from client_orders_details where id_order = ".$id1." 
                            and is_deleted = 0) tt1 on tt.id_product = tt1.id_product";
                }
                
                if(isset($arr[1]['id'])){
                    $id2 = $arr[1]['id'];
                    $q1 = "ifnull(tt1.amount, 0) as order1,
                           ifnull(tt2.amount, 0) as order2,
                           0 as order3,
                           0 as order4,";
                    
                    $q2 = "left join (select id_product, amount 
                            from client_orders_details where id_order = ".$id1." 
                            and is_deleted = 0) tt1 on tt.id_product = tt1.id_product
                            
                            left join (select id_product, amount 
                            from client_orders_details where id_order = ".$id2." 
                            and is_deleted = 0) tt2 on tt.id_product = tt2.id_product";
                }
                
                if(isset($arr[2]['id'])){
                    $id3 = $arr[2]['id'];
                    $q1 = "ifnull(tt1.amount, 0) as order1,
                           ifnull(tt2.amount, 0) as order2,
                           ifnull(tt3.amount, 0) as order3,
                           0 as order4,";
                    
                    $q2 = "left join (select id_product, amount 
                            from client_orders_details where id_order = ".$id1." 
                            and is_deleted = 0) tt1 on tt.id_product = tt1.id_product
                            
                            left join (select id_product, amount 
                            from client_orders_details where id_order = ".$id2." 
                            and is_deleted = 0) tt2 on tt.id_product = tt2.id_product
                            
                            left join (select id_product, amount 
                            from client_orders_details where id_order = ".$id3." 
                            and is_deleted = 0) tt3 on tt.id_product = tt3.id_product";
                }
                
                if(isset($arr[3]['id'])){
                    $id4 = $arr[3]['id'];
                    $q1 = "ifnull(tt1.amount, 0) as order1,
                           ifnull(tt2.amount, 0) as order2,
                           ifnull(tt3.amount, 0) as order3,
                           ifnull(tt4.amount, 0) as order4,";
                    
                    $q2 = "left join (select id_product, amount 
                            from client_orders_details where id_order = ".$id1." 
                            and is_deleted = 0) tt1 on tt.id_product = tt1.id_product
                            
                            left join (select id_product, amount 
                            from client_orders_details where id_order = ".$id2." 
                            and is_deleted = 0) tt2 on tt.id_product = tt2.id_product
                            
                            left join (select id_product, amount 
                            from client_orders_details where id_order = ".$id3." 
                            and is_deleted = 0) tt3 on tt.id_product = tt3.id_product
                            
                            left join (select id_product, amount 
                            from client_orders_details where id_order = ".$id4." 
                            and is_deleted = 0) tt4 on tt.id_product = tt4.id_product";
                }
            }
            
            $queryTxt = "select 
                            tt.id_product, 
                            tt.grp_name, 
                            tt.alias, 
                            
                            ".$q1."
                            
                            round( if(tt.disp = 0, 
                                (tt.base_price-(tt.base_price/100)*tt.disg),
                                (tt.base_price-(tt.base_price/100)*tt.disp)
                            ), 2) as price,
                            
                            if(".$isQuantity." = 1, 
                                tt.quantity, 
                                0) as quantity,
                            
                            ifnull(tt0.amount, 0) as amount,
                            
                            ((round( if(tt.disp = 0, 
                                (tt.base_price-(tt.base_price/100)*tt.disg),
                                (tt.base_price-(tt.base_price/100)*tt.disp)
                            ), 2)) * ifnull(tt0.amount, 0)) as total
                            
                        from     
                            (select 
                                tA.*, 
                                if(tB.discount is not null, 
                                    tB.discount, 
                                    0) as disg,
                                if(tC.discount is not null, 
                                    tC.discount, 
                                    0) as disp 
                            from 
                                (select 
                                    t1.id_product, 
                                    t1.id_grp, 
                                    t2.alias as grp_name, 
                                    t1.alias, 
                                    t1.base_price,
                                    t1.quantity 
                                from
                                    client_".$idAgent."_products t1 
                                left join 
                                    client_".$idAgent."_groups t2
                                on 
                                    t1.id_grp = t2.id_grp 
                                where 
                                    t1.is_deleted = 0) tA
                            left join 
                                (select 
                                    id_grp, 
                                    discount 
                                from 
                                    client_".$idAgent."_disg 
                                where 
                                    id_price = (select distinct 
                                                    id_price 
                                                from  
                                                    client_".$idAgent."_clients 
                                                where 
                                                    id_client = ".$idClient." 
                                                and 
                                                    is_deleted = 0) 
                                and
                                    clmn = (select distinct 
                                                clmn 
                                            from  
                                                client_".$idAgent."_clients 
                                            where 
                                                id_client = ".$idClient." 
                                            and 
                                                is_deleted = 0)) tB
                            on 
                                tA.id_grp = tB.id_grp
                            left join
                                (select 
                                    id_product, 
                                    discount 
                                from 
                                    client_".$idAgent."_disp 
                                where 
                                    id_price = (select distinct 
                                                    id_price 
                                                from  
                                                    client_".$idAgent."_clients 
                                                where 
                                                    id_client = ".$idClient." 
                                                and 
                                                    is_deleted = 0) 
                                and
                                    clmn = (select distinct 
                                                clmn 
                                            from 
                                                client_".$idAgent."_clients 
                                            where 
                                                id_client = ".$idClient." 
                                            and 
                                                is_deleted = 0)) tC
                            on 
                                tA.id_product = tC.id_product) tt
                            
                            left join
                                
                                (select id_product, 
                                        amount 
                                from 
                                    client_orders_details
                                where 
                                    id_order = ".$idOrder." 
                                and is_deleted = 0) tt0
                            on 
                                tt.id_product = tt0.id_product ".$q2." 
                        order by
                            tt.grp_name, tt.alias";
            //$rowsArr = $this->my_query($queryTxt,'ARR');
            //$aaa['comment'] = 'ССССИИИИ'; 
            //$ret = "{\"rows\":".json_encode($rowsArr).",
			//	\"mdt\":".json_encode($aaa)."}";
            $ret = $this->my_query($queryTxt,'JSON');
        }else{
            $queryTxt = "select 
                            tt1.id_product,
                            tt2.alias as grp_name,
                            tt1.alias,
                            '--' as column1, 
                            '--' as column2, 
                            '--' as column3, 
                            '--' as column4,
                            '0' as quantity,
                            tt1.price,
                            tt1.amount,
                            tt1.total
                        from
                            (select 
                                t1.id_product, 
                                t2.id_grp, 
                                t2.alias, 
                                t1.price,
                                t1.amount,
                                t1.total
                            from 
                                client_orders_details t1
                            left join
                                client_".$idAgent."_products t2
                            on
                                t1.id_product = t2.id_product
                            where 
                                t1.id_order = ".$idOrder."
                            and
                                t1.is_deleted = 0) tt1
                        left join
                            client_".$idAgent."_groups tt2
                        on 
                            tt1.id_grp = tt2.id_grp"." 
                        order by
                            tt2.alias, tt1.alias";
            
            //$rowsArr = $this->my_query($queryTxt,'ARR');
            //$aaa['comment'] = 'ССССИИИИ'; 
            //$ret = "{\"rows\":".json_encode($rowsArr)."
			//	\"metaData\":\"".json_encode($aaa)." \"}";
			$ret = $this->my_query($queryTxt,'JSON');
			//$ret = $queryTxt;
        }
		return $ret;
	}
 
    function get_groups($idAgent){
        $queryTxt = "(select t1.alias as grp_name from 
                        (select distinct id_grp from 
                        client_".$idAgent."_products 
                        where 
                        is_deleted = 0) t
                        left join
                        client_".$idAgent."_groups t1 
                        on t.id_grp = t1.id_grp)
                    union
                    (select 'Все группы' as grp_name)";
        $ret = $this->my_query($queryTxt,'JSON');
        return $ret;
    }
    
    function get_orders(){
		$queryTxt = "select distinct id_agent from client_orders
						where is_deleted = 0";
		$arr = $this->my_query($queryTxt,'ARR');
		
		
		$qArr = array();
		foreach ($arr as $row){
			$idAgent = $row['id_agent'];
			
			$q = "(select id_client, alias from
					client_".$idAgent."_clients)";
			$qArr[] = $q;
		}
		$unions = "(".implode(" union ",$qArr).") t1";
        
        $queryTxt = "select 
						t.id as id_order,
                        t.id_amt, 
                        t.id_agent, 
                        t.id_client, 
                        t1.alias as cln_name, 
                        date_format(t.dt,'%d.%m.%y') as dt,
                        t.tm,
                        round(t.total, 2) as total,
                        t.comment,
                        if(t.d_date = '0000-00-00','',date_format(t.d_date,'%d.%m.%y')) as d_date,
                        
                        if(t.is_sended = 1,
                           'отправлен', 
                           if(t.is_accepted = 1, 
                              'прин. к отпр.', 
                              'сохранен'
                           )
                        ) as state
                    from
                    client_orders t
					left join ".$unions."
					on t.id_client = t1.id_client
                    where t.is_deleted = 0
					order by  t.dt desc, t.tm desc";
	$ret = $this->my_query($queryTxt,'JSON');
    return $ret;
    }
	
	function accept_order($idOrder){
		$updateTxt = "update client_orders set is_accepted = 1,
						dt = date(now()), 
						tm = time(now()) where id = ".$idOrder;
		 $this->my_query($updateTxt);
	}
	
    function resend_order($idOrder){
		$updateTxt = "update client_orders set is_sended = 0,
						dt = date(now()), 
						tm = time(now()) where id = ".$idOrder;
		 $this->my_query($updateTxt);
	}
    
    function delete_order($idOrder){
		$updateTxt = "update client_orders set is_deleted = 1,
						dt = date(now()), 
						tm = time(now()) where id = ".$idOrder;
        $this->my_query($updateTxt);
        
        $updateTxt = "update client_orders_details set is_deleted =1
                        where id_order = ".$idOrder;
        $this->my_query($updateTxt); 
	}
    
    function get_params(){
        $resArr = array();
        
        $queryTxt = "select alias 
                        from client_license where id = 1
                        limit 1";
        $arr = $this->my_query($queryTxt,'ARR');
        if(is_array($arr)){
            $resArr['license'] = $arr[0]['alias'];
        }else{
            $resArr['license'] = '';
        }
        
        $queryTxt = "select val 
                        from client_params where param like 'address'
                        limit 1";
        $arr = $this->my_query($queryTxt,'ARR');
        if(is_array($arr)){
            $resArr['address'] = $arr[0]['val'];
        }else{
            $resArr['address'] = '';
        }
        
        $queryTxt = "select val 
                        from client_params where param like 'port'
                        limit 1";
        $arr = $this->my_query($queryTxt,'ARR');
        if(is_array($arr)){
            $resArr['port'] = $arr[0]['val'];
        }else{
            $resArr['port'] = '';
        }

        $ret = json_encode($resArr);
        
        return $ret;
    }
    
    function set_params($paramsArr){
        if(isset($paramsArr[0]) && $paramsArr[0]!='none' ){
            $insTxt = "insert into client_license 
                            (id,alias,last_sync_date,last_sync_time)
                            values
                            (1, '".$paramsArr[0]."',
                            date(now()), time(now()))
                        on duplicate key update
                            alias = '".$paramsArr[0]."',
                            last_sync_date = date(now()),
                            last_sync_time = time(now())";
            $this->my_query($insTxt);
        }
        
        if(isset($paramsArr[1]) && $paramsArr[1]!='none' ){
            $insTxt = "insert into client_params
                        values ('address', '".$paramsArr[1]."')
                        on duplicate key update
                            val = '".$paramsArr[1]."'";
            $this->my_query($insTxt);
        }
        if(isset($paramsArr[1]) && $paramsArr[2]!='none' ){
            $insTxt = "insert into client_params
                        values ('port', '".$paramsArr[2]."')
                        on duplicate key update
                            val = '".$paramsArr[2]."'";
            $this->my_query($insTxt);
        }
    }
    
    function test_orders(){
        $queryTxt = "select id from client_orders where is_accepted = 1
                        and is_sended = 0 and is_deleted = 0";
        $arr = $this->my_query($queryTxt,'ARR');
        if(is_array($arr)){
            $ret = 'presist';
        }else{
            $ret = 'none';
        }
        return $ret;
    }
    
    function get_orders_tosend(){
        $a =0;
        $b=0;
        $headTxt = "select id as i, id_agent as a, id_client as c, 
                        dt as d, tm as t, d_date  as dd, total as ttl,
                        comment as cmt   
                        from client_orders where is_deleted = 0 and is_sended = 0 and is_accepted = 1";
        $arr = $this->my_query($headTxt,"ARR");
        
        if (is_array($arr)){
            $resultArray[0]=$arr;
            $a = 1;
        }
        
        $detTxt = "select id_order as i, id_product as p, amount as a from client_orders_details where id_order in 
                    (select id from client_orders where is_deleted = 0 and is_sended = 0 and is_accepted = 1) and 
                    is_deleted = 0";
        $arr =  $this->my_query($detTxt,"ARR");
        
        if (is_array($arr)){
            $resultArray[1]=$arr;
            $b = 1;
        }
        
        if (($a+$b) == 2){
            return $resultArray;
        }
        else{
            return 'error';
        }
    
    
    }
    
    function get_params_sync(){
        $resArr = array();
        
        $queryTxt = "select alias 
                        from client_license where id = 1
                        limit 1";
        $arr = $this->my_query($queryTxt,'ARR');
        if(is_array($arr)){
            $resArr['license'] = $arr[0]['alias'];
        }else{
            $resArr['license'] = '';
        }
        
        $queryTxt = "select val 
                        from client_params where param like 'address'
                        limit 1";
        $arr = $this->my_query($queryTxt,'ARR');
        if(is_array($arr)){
            $resArr['address'] = $arr[0]['val'];
        }else{
            $resArr['address'] = '';
        }
        
        $queryTxt = "select val 
                        from client_params where param like 'port'
                        limit 1";
        $arr = $this->my_query($queryTxt,'ARR');
        if(is_array($arr)){
            $resArr['port'] = $arr[0]['val'];
        }else{
            $resArr['port'] = '';
        }
        return $resArr;
    }
    
    function set_sended(){
        $updTxt = "update client_orders set is_sended = 1 
                    where is_accepted = 1";
        $this->my_query($updTxt);
    }
 }
