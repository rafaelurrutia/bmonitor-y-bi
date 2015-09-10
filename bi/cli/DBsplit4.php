<?php
//vesion 4, actualización 2015/03/24 utilizada en la actualidad.
if (php_uname("s") == "Darwin") 
	$connect = mysql_connect('127.0.0.1', 'root', '') or die('Could not connect: ' . mysql_error());
else 
	$connect = mysql_connect('127.0.0.1', 'root', 'bsw$$2009') or die('Could not connect: ' . mysql_error());
	
	
$bsw_bi=mysql_select_db('bsw_bi', $connect);
$bds=mysql_query("select * from bi_server where active=true order by dbName desc");

while ($bd = mysql_fetch_array($bds,MYSQL_ASSOC))
{
	$go=false;
	if (count($argv) > 1) {
		if (array_search( $bd['dbName'], $argv) > 0) $go=true;
  	}
	else
	  $go=true;
	if ($go) {
		$bool = mysql_select_db($bd['dbName'], $connect);
		echo '========> ' . $bd['dbName'] . " version 4. \n";
		mysql_query('drop table IF EXISTS bi_aggregate');
		mysql_query('drop table IF EXISTS bi_date');
		mysql_query('drop table IF EXISTS bi_day');
		mysql_query('drop table IF EXISTS bi_history');
		mysql_query('drop table IF EXISTS bi_host');
		mysql_query('drop table IF EXISTS bi_hour');
		mysql_query('drop table IF EXISTS bi_hour_plan');
		mysql_query('drop table IF EXISTS bi_stat');
		mysql_query('drop table IF EXISTS bi_stat2');
		mysql_query('delete from bi_dashboard where id_item not in (select id_item from bm_items)');
			
		  $cnt=0;
          $hostr=mysql_query('select id_host from bm_host where borrado=0');
   	      while ($hosts = mysql_fetch_array($hostr,MYSQL_ASSOC)) {
			  $hostid=$hosts['id_host'];
			  $add=false;
			  //$items='select * from bi_dashboard order by id_item'; 
			  //$items='SELECT PV.id_monitor as id_item from bm_profiles_category PC LEFT JOIN bm_profiles_values PV ON PV.id_category=PC.id_category WHERE PC.`status`=TRUE AND PV.id_monitor>0 order by id_item asc';  //antes
				$items='SELECT DISTINCT PV.`id_monitor` as id_item FROM `bm_profiles` P
				LEFT JOIN `bm_profiles_categories`  PCIES ON P.`id_profile` = PCIES.`id_profile`
				LEFT JOIN `bm_profiles_item` PI ON PI.`id_categories`=PCIES.`id_categories`
				LEFT JOIN `bm_profiles_category` PCT ON PCIES.`id_categories`= PCT.`id_categories`
				LEFT JOIN `bm_profiles_values` PV ON (PI.`id_item`=PV.`id_item` AND PCT.`id_category`=PV.`id_category`)
				LEFT OUTER JOIN (SELECT `id_value`,`value` FROM `bm_profiles_host` WHERE `id_host` = '.$hostid.') AS PHOST ON PV.`id_value`=PHOST.`id_value`
				WHERE P.`id_profile` > 0 AND PCT.`status` = TRUE GROUP BY PV.`id_value`';				
			  //echo"--->nueva consulta: ".$items; 
			  $itemsr=mysql_query($items);
			  $max=-1;
			  $nhosts=mysql_num_rows($hostr);
			  while ($item = mysql_fetch_array($itemsr,MYSQL_ASSOC))
			  {
				$table='xyz_' . str_pad($item['id_item'], 10, "0", STR_PAD_LEFT);		
				$createIndex = false;
				if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . $table . "'"))==1) 
				{
					$maxClock = mysql_query('select max(clock) as clock from ' . $table . ' where id_host=' . $hostid);
					$max=mysql_fetch_array($maxClock,MYSQL_ASSOC);
					$max=$max['clock'];
					if (!isset($max)) $max=0;
					$query  = 'insert into ' . $table . ' select clock,id_host,value,valid from bm_history where id_item=' . $item['id_item'] . ' and id_host=' . $hostid . ' and clock > ' . $max;
				}
				else {
					$query  = 'create table ' . $table . ' select clock,id_host,value,valid from bm_history where id_item=' . $item['id_item'] . ' and id_host=' . $hostid;	
					$createIndex=true;
				}
				$result=mysql_query($query);
				$nr= mysql_affected_rows();
				echo round($cnt/$nhosts*100,2) . "% " . $table . " " . $bd['dbName'] . " " . str_pad($hostid, 10, "0", STR_PAD_LEFT) . " " . $max;
		 		if (mysql_errno() != "") echo 'Error:' . mysql_error() . "\n";
				if ($createIndex) 
				{
					if (mysql_affected_rows() == 0) {
						mysql_query('drop table ' . $table);
						echo " No data. Deleted\n";
					}
					else {
						mysql_query('create index ' . $table . '_idx on ' . $table . '(clock,id_host)');
						echo " Added with " . $nr . " record(s)\n";
					}
				}
				else {
					echo " Updated with " . $nr . " record(s)\n";
				}
			  }
	 		  $cnt++;	
        }
	}
	else
	{
		 echo '========> ' . $bd['dbName'] . " skipped\n";
	}		
}
?>
