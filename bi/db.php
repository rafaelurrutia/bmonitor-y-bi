<?php

try {
	if (php_uname("s") != "Darwin")
 	{
  		$connect = @mysql_connect('localhost', 'root', 'bsw$$2009') or die('Could not connect: ' . mysql_error());
 	}
 	else
 	{
 		$connect = mysql_connect('localhost', 'root', '') or die('Could not connect: ' . mysql_error());
 	}
	if (php_uname("s") == "Darwin")
		$NameBD = "bmonitor";
	else
		    $NameBD = $cmd->parametro->get("NameBD");
	$bool = mysql_select_db($NameBD, $connect);
 	$q = mysql_query('select * from bsw_bi.bi_server where dbName="' . $NameBD . '"');
 	if (mysql_num_rows($q) == 1)
    	$_SESSION['id_server'] = mysql_result($q, 0);
 	else
   		die($LANG_ERROR_BI);

} catch (Exception $e) {
    print $LANG_ERROR_CONNECT . " $database";
}

include_once "fast/phpfastcache/phpfastcache.php";
phpFastCache::setup("storage","auto");
if (php_uname("s") == "Darwin") phpFastCache::setup("path", '/private/var/tmp'); // Path For Files includes/work must be in 777
function readDashboardSections() {
	$q='select distinct dashboard
	from bi_dashboard,bm_items_groups
	where bi_dashboard.id_item=bm_items_groups.id_item and
		  bm_items_groups.groupid='. $_SESSION['groupid'] . '
	order by displayorder';
	$q1=mysql_query($q);
	while ($r = mysql_fetch_array($q1, MYSQL_ASSOC))  {
		$data[] = array('dashboard'=>$r['dashboard']);
	}
	return $data;
}

function readDashboard($dashboard) {
	$d="select bm_items.descriptionLong,bm_items.id_item from bi_dashboard,bm_items,bm_items_groups
	where bi_dashboard.id_item=bm_items.id_item
	and bi_dashboard.dashboard='" . $dashboard . "'
	and bm_items_groups.id_item=bm_items.id_item
	and bm_items_groups.groupid=" . $_SESSION['groupid'] . ' order by bm_items.descriptionLong';

	$dash = mysql_query($d);
    while ($items = mysql_fetch_array($dash, MYSQL_ASSOC)) {
    	$data[]=array('descriptionLong'=>$items['descriptionLong'],'id_item'=>$items['id_item']);
	}
	return $data;
}

function readPlan() {
	$getPlanSQL = "select P.`plan`, P.`id_plan` , P.`nacD`, P.`nacU`
					from bm_plan P ,bm_plan_groups PG
					where P.`id_plan`=PG.`id_plan` and PG.borrado=0 and PG.groupid=".$_SESSION['groupid']."
					order by nacD";
	$getPlanResult=mysql_query($getPlanSQL);

	while ($plans = mysql_fetch_array($getPlanResult,MYSQL_ASSOC)) {
		$data[]=array('plan'=>$plans['plan'],'id_plan'=>$plans['id_plan'],'nacD'=>$plans['nacD'],'nacU'=>$plans['nacU']);
	}
	return $data;
}

function readMonitors() {
	$items_rs_sql = "SELECT IT.`id_item`, IT. `unit`,  PCG.`id_categories`, PCG.`category` , REPLACE(IT.`descriptionLong`, CONCAT(PCG.`category`,' -'), '')  as 'items' , IT.`descriptionLong`
				FROM `bm_items` IT
					INNER JOIN `bm_items_groups`  IG ON IT.`id_item` =IG.`id_item`
					INNER JOIN `bm_profiles_values` PV ON PV.`id_monitor`=IT.`id_item`
					INNER JOIN `bm_profiles_category` PC ON PV.`id_category`=PC.`id_category`
					INNER JOIN `bm_profiles_categories` PCG ON PCG.`id_categories`=PC.`id_categories` and IT.id_item in (select id_item from bi_dashboard)
			WHERE IG.`groupid` = ".$_SESSION['groupid']." AND PC.`view` = 'true'
			GROUP BY IT.`id_item`
			ORDER BY PCG.`category`,items";
	$items_rs = mysql_query($items_rs_sql);
	while ($items = mysql_fetch_array($items_rs, MYSQL_ASSOC)) {
		$data[] = array('id_item'=>$items['id_item'],'descriptionLong'=>$items['descriptionLong'],'unit'=>$items['unit']);
	}
	return $data;
}

function readMonitorsUsedOnBI() {
	$q='select bm_items.id_item,descriptionLong
                             from bm_items,bm_items_groups
                             where bm_items.id_item in (select id_item from bm_threshold) and
                                   bm_items.id_item=bm_items_groups.id_item and
                                   bm_items_groups.groupid = ' . $_SESSION['groupid'] . '
                             order by descriptionLong';

    $items_rs = mysql_query($q);
    while ($items = mysql_fetch_array($items_rs, MYSQL_ASSOC)) {
    	$data[]=array('id_item'=>$items['id_item'],'descriptionLong'=>$items['descriptionLong']);
    }
	return $data;
}

function readMonitorsAvailableForBI() {
	$q1='select distinct bm_items.id_item,descriptionLong
        from bm_items,bm_profiles_values,bm_items_groups
        where bm_items.id_item=bm_profiles_values.id_monitor and
            bm_items.id_item not in (select id_item from bm_threshold)  and
            bm_items.id_item=bm_items_groups.id_item and unit <>"" and unit is not null and
            bm_items_groups.groupid = ' . $_SESSION['groupid'] . '
        order by descriptionLong';

    $items_rs = mysql_query($q1);
	while ($items = mysql_fetch_array($items_rs, MYSQL_ASSOC)) {
    	$data[]=array('id_item'=>$items['id_item'],'descriptionLong'=>$items['descriptionLong']);
    }
	return $data;
}

function readGraphs() {
	$graphs = mysql_query('select distinct gh.`name`, gh.`id_graph`
                         from bm_graphs gh
                         where gh.groupid=' . $_SESSION['groupid'] . '
                         order by gh.name');
	while ($graph = mysql_fetch_array($graphs, MYSQL_ASSOC)) {
		$data[]=array('name'=>$graph['name'],'id_graph'=>$graph['id_graph']);
	}
	return $data;
}

function readMonitorsForGraph($id_graph) {
	$items_rs = mysql_query("SELECT  `bm_items`.`descriptionLong`, bm_graphs_items.`id_item` , `bm_items`.`unit`
							FROM bm_graphs,bm_graphs_items,bm_items
							WHERE bm_graphs.`id_graph`=bm_graphs_items.`id_graph` AND bm_graphs_items.`id_item`=`bm_items`.`id_item` AND bm_graphs.`id_graph` = ". $id_graph);

	while ($items = mysql_fetch_array($items_rs, MYSQL_ASSOC)) {
		$data[] = array('descriptionLong'=>$items['descriptionLong'],'id_item'=>$items['id_item'],'unit'=>$items['unit']);
	}
	return $data;
}

function readHosts($id_plan) {
	if ($id_plan>0)
		$q1 = mysql_query("select host,id_host from bm_host where id_plan=" . $id_plan . " and status=1 AND borrado=0  AND `groupid` = ".$_SESSION['groupid']);
	else
 		$q1 = mysql_query("select host,id_host from bm_host where status=1 AND borrado=0  AND `groupid` = ".$_SESSION['groupid']);

	while ($r = mysql_fetch_array($q1, MYSQL_ASSOC)) {
		$data[] = array('id_host'=>$r['id_host'],'host'=>$r['host']);
	}
	return $data;
}

function loadRAWData($from,$id_plan,$id_item,$to = null, $id_host=null) {
	$cacheAge=60*60;
	$offset=getOffset();
  	setTimezone();
    $cache = phpFastCache();
	//$cache->clean();
  	$dt=getDates($from);
  	$from=$dt[0];

	if ($to == null) $to=$dt[1];
	$from=str_replace("-", "/", $from);
	$to=str_replace("-", "/", $to);
	$phour=getPeakHours();
	$unit=getUnit($id_item);
	$plan=getPlan($id_plan);
	$item=getItem($id_item);
 	$div = 1;
  	if (strtolower($unit) == 'bps') {
		$div = (1024 * 1024);
		$unit = 'Mbps';
  	}

  	$rtag="";
  	foreach ($_SESSION['tag'] as $key=>$value) {
	if ($key != "" && $key != "None") {
			$rtag = $rtag . ',"' . $key . '"';
		}
  	}
  	if ($rtag == '')  $rtag = 'bm_tags.tag like "%"';
  	else $rtag = 'bm_tags.tag in (' . substr($rtag, 1) . ')';

	if ($id_plan == 0)
		$id_plan = '> 0';
	else
  		$id_plan = '=' . $id_plan;
	if ($id_host == null)
		$id_host="";
	else
		$id_host= " and bm_host.id_host=" . $id_host;
	$hosts='select bm_host.id_host,bm_host.id_plan,bm_plan.plan,bm_plan.nacD,bm_plan.nacU,bm_threshold.critical,bm_threshold.warning,bm_threshold.nominal,bm_host.host
						from bm_host,bm_plan,bm_plan_groups,bm_threshold,bi_dashboard
						where bm_host.id_plan=bm_plan.id_plan
							and bm_host.groupid = ' . $_SESSION['groupid'] . '
							and bm_plan.id_plan ' . $id_plan . '
							and bm_plan_groups.id_plan = bm_plan.id_plan
							and bm_host.borrado=0
							and bm_host.id_plan=bm_plan.id_plan
							and bm_plan_groups.groupid=bm_host.groupid
							and bm_threshold.id_item=bi_dashboard.id_item
							and bm_host.id_host in (select id from bm_tags where ' . $rtag . ')
							and bi_dashboard.id_item=' . $id_item . $id_host . '
						order by bm_host.id_plan';
						//var_dump($hosts);

 	// Find Percentile 5 and 95
  	if (true) {
	  	$hostsr=mysql_query($hosts);

	  	while ($host = mysql_fetch_array($hostsr,MYSQL_ASSOC)) {
			$id_host=$host['id_host'];
			$hostName=$host['host'];
			$nominal=$host['nominal'];
			$critical=$host['critical'];
			$warning=$host['warning'];
			$nacD=$host['nacD']*1024;
			$nacU=$host['nacU']*1024;
			if ($nominal == -1) $nominal = $nacD;
			if ($nominal == -2) $nominal = $nacU;
			$hostList[]=array('host'=>$hostName,'id_host'=>$id_host,'nominal'=>$nominal,'critical'=>$critical,'warning'=>$warning,'nacD'=>$nacD,'nacU'=>$nacU);
			$incache=true;
			unset($cachedData);
			$dfrom=$key=date('Y/m/d',strtotime($from . ' -1 days'));
			while ($dfrom != $to) {
				$dfrom=date('Y/m/d',strtotime($dfrom . ' +1 days'));
				$key=$dfrom . '-' . $id_host . '-' . $id_item;
				//if (isset($_SESSION['cache'][$key]))
				//	$obj = $_SESSION['cache'][$key];
				//else
				//echo $key . "<br>";
				$obj = $cache->get($key);
				if ($obj == null)
				{
					$incache=false;
					$_SESSION['cacheFull']=false;
				}
				else {
					$_SESSION['cachePartial']=true;
					foreach($obj as $key=>$value) {
						if (isset($value['clock']) ) $cachedData[]=$value;
					}
				}
			}
			if (!isset($cachedData)) $incache=false;
			if ($incache) {
				foreach($cachedData as  $key=>$row) {
					$skip = false;
					if (isset($_SESSION['filter']['< 1.5x'])&& $row['value'] > $nominal*1.5) $skip=true;
					if (isset($_SESSION['filter']['< 2.0x'])&& $row['value'] > $nominal*2.0) $skip=true;
					if (isset($_SESSION['filter']['< 3.0x']) && $row['value'] > $nominal*3.0) $skip=true;
					if (isset($_SESSION['filter']['> 0']) && $row['value'] <=0) $skip=true;
					if (isset($_SESSION['filter']['Off Peak Hour']) && $row['t'] == 'P') $skip=true;
					if (isset($_SESSION['filter']['Peak Hour']) && $row['t'] == 'O') $skip=true;

					if (!$skip)	{
						$allData[]=$row['value'];
						$historyList[$id_host][] = array('clock'=>$row['clock'],'value'=>$row['value'],'valid'=>$row['valid'],'t'=>$row['t']);
					}
				}
			}
			else {
				$table='xyz_' . str_pad($id_item, 10, "0", STR_PAD_LEFT);
				//var_dump($table);
				if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . $table . "'"))==1) {
					if ($phour['peak'] > $phour['offpeak'])
								$q='select clock,value,valid,if((date_format(from_unixtime(clock),"%H")>=0 and date_format(from_unixtime(clock),"%H")<=' . $phour['offpeak'] . ') or date_format(from_unixtime(clock),"%H")>=' . $phour['peak'] . ',"P","O") as t
							from ' . $table . '
							where clock between unix_timestamp("' . $from . ' 00:00:00") AND unix_timestamp("' . $to . ' 23:59:59") and
								id_host=' . $id_host;
					else
						$q='select clock,value,valid,if(date_format(from_unixtime(clock),"%H")>=' . $phour['peak'] . ' and date_format(from_unixtime(clock),"%H")<=' . $phour['offpeak']. ',"P","O") as t
							from ' . $table . '
							where clock between unix_timestamp("' . $from . ' 00:00:00") AND unix_timestamp("' . $to . ' 23:59:59") and
								id_host=' . $id_host;
					//var_dump($q);
					$qr=mysql_query($q);

					while ($row = mysql_fetch_array($qr, MYSQL_ASSOC)) {
						$key=date('Y/m/d',$row['clock']);
						$data[$key][$id_host . '-' . $id_item][] = array('clock'=>$row['clock'],'value'=>$row['value'],'valid'=>$row['valid'],'t'=>$row['t']);
						$skip = false;
						if (isset($_SESSION['filter']['< 1.5x'])&& $row['value'] > $nominal*1.5) $skip=true;
						if (isset($_SESSION['filter']['< 2.0x'])&& $row['value'] > $nominal*2.0) $skip=true;
						if (isset($_SESSION['filter']['< 3.0x']) && $row['value'] > $nominal*3.0) $skip=true;
						if (isset($_SESSION['filter']['> 0']) && $row['value'] <=0) $skip=true;
						if (isset($_SESSION['filter']['Off Peak Hour']) && $row['t'] == 'P') $skip=true;
						if (isset($_SESSION['filter']['Peak Hour']) && $row['t'] == 'O') $skip=true;

						if (!$skip)	{
							$allData[]=$row['value'];
							$historyList[$id_host][] = array('clock'=>$row['clock'],'value'=>$row['value'],'valid'=>$row['valid'],'t'=>$row['t']);
						}
					}
	  			}

				// check miising data
				$dfrom=$key=date('Y/m/d',strtotime($from . ' -1 days'));
				while ($dfrom != $to) {
					$dfrom=date('Y/m/d',strtotime($dfrom . ' +1 days'));
					$key=$dfrom . '-' . $id_host . '-' . $id_item;

					$obj=$cache->get($key);
					if ($obj == null) {
						$cache->set($key, array('clock'=>0,'value'=>0,'valid'=>0,'t'=>''),$cacheAge);
						//$_SESSION['cache'][$key]= array('clock'=>0,'value'=>0,'valid'=>0,'t'=>'');
					}
				}
	  		}
	  	}
  	}
	if (isset($incache) && !$incache && isset($data)) {
		foreach ($data as $key=>$value)
		{
			foreach ($value as $hkey=>$hvalue)
			{
				$thekey=$key . '-' . $hkey;
				//echo $thekey . ' ';
				$cache->set($thekey, $hvalue,$cacheAge);
				//$_SESSION['cache'][$thekey] = $hvalue;
			}
		}
	}
	if (isset($allData))
		return array('allData'=>$allData,'historyList'=>$historyList,'hostList'=>$hostList,'offpeak'=>$phour['offpeak'],'peak'=>$phour['peak'],'unit'=>$unit,'div'=>$div,'plan'=>$plan,'item'=>$item,'from'=>$from,'to'=>$to);
	else
		return array('allData'=>null,'historyList'=>null,'hostList'=>null,'offpeak'=>$phour['offpeak'],'peak'=>$phour['peak'],'unit'=>$unit,'div'=>$div,'plan'=>$plan,'item'=>$item,'from'=>$from,'to'=>$to);
}

function getIdItemUnit($descriptionLong)
{
    $qunit = mysql_query('select distinct unit from bm_items where id_item="' . $descriptionLong . '"');
    if (mysql_num_rows($qunit) > 0)
        $unit = mysql_result($qunit, 0);
    else
        $unit = '';
    return $unit;
}

function getUnit($id_item)
{
    $qunit = mysql_query('select distinct unit from bm_items where id_item="' . $id_item . '"');
    if (mysql_num_rows($qunit) > 0)
        $unit = mysql_result($qunit, 0);
    else
        $unit = '';
    return $unit;
}

function getPeakHours()
{
    $q = mysql_query('select valor from Parametros where nombre="PEAK_HOUR_START"');
    if (mysql_num_rows($q) == 1)
        $peak = mysql_result($q, 0);
    else
        $peak = '18';
    $q = mysql_query('select valor from Parametros where nombre="PEAK_HOUR_END"');
    if (mysql_num_rows($q) == 1)
        $offpeak = mysql_result($q, 0);
    else
        $offpeak = '22';
    return array('peak'=>str_replace(':00','',$peak),'offpeak'=>str_replace(':00','',$offpeak));
}

function getIdPlan($plan)
{
    $qid_plan = mysql_query('select id_plan from bm_plan where plan="' . $plan . '"');
    if (mysql_num_rows($qid_plan) == 1)
        $id_plan = mysql_result($qid_plan, 0);
    else
        $id_plan = -1;
    return $id_plan;
}

function getPlan($idplan)
{
    $qid_plan = mysql_query('select plan from bm_plan where id_plan="' . $idplan . '"');
    if (mysql_num_rows($qid_plan) == 1)
        $id_plan = mysql_result($qid_plan, 0);
    else
        $id_plan = -1;
    return $id_plan;
}

function getItem($iditem)
{
    $qid_item = mysql_query('select descriptionLong from bm_items where id_item="' . $iditem . '"');
    if (mysql_num_rows($qid_item) == 1)
        $id_item = mysql_result($qid_item, 0);
    else
        $id_item = -1;
    return $id_item;
}

function getDescriptionLong($iditem)
{
    $qid_item = mysql_query('select descriptionLong from bm_items where id_item="' . $iditem . '"');
    if (mysql_num_rows($qid_item) == 1)
        $id_item = mysql_result($qid_item, 0);
    else
        $id_item = -1;
    return $id_item;
}

function getThreasholds($id_item) {
	$descriptionLong=getDescriptionLong($id_item);
	$q = 'select nominal,warning,critical from bm_threshold where id_item=' . $id_item;
	$qq = mysql_query($q);
	while ($p = mysql_fetch_array($qq, MYSQL_ASSOC)) {
		$data = array('nominal'=>$p['nominal'],'warning'=>$p['warning'],'critical'=>$p['critical'],'descriptionLong'=>$descriptionLong,'id_item'=>$id_item);
	}
	return $data;
}

function getIdItem($descriptionLong)
{
    $qid_item = mysql_query('select id_item from bm_items where descriptionLong="' . $descriptionLong . '"');
    if (mysql_num_rows($qid_item) == 1)
        $id_item = mysql_result($qid_item, 0);
    else
        $id_item = -1;
    return $id_item;
}

function printName()
{
    $q = 'select valor from Parametros where nombre="SITE"';
    $qgmt = mysql_query($q);
    $name = mysql_result($qgmt, 0);
    //var_dump($name);
    return $name . ' by Baking Software QoS BI';
}

function getTimeZoneOffset() {
	$qoffset = 'select valor from Parametros where nombre="TIMEZONE"';
    $qresult = mysql_query($qoffset);
    $gmt = mysql_result($qresult, 0);
	return $gmt;

}

function setTimeZoneOffset($offset) {
	$qset = "SET time_zone = '" . $offset . "'";
    mysql_query($qset);
}

function getGroups() {
	$xp = mysql_query('select g.name,g.groupid
                                from bm_host_groups g
                                where g.borrado=0
                                order by g.name DESC');
	while ($p = mysql_fetch_array($xp, MYSQL_ASSOC)) {
		$data[] = array('name'=>$p['name'],'groupid'=>$p['groupid']);
	}
	return $data;
}
?>
