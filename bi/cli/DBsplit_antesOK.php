<?php

if (php_uname("s") == "Darwin") 
	$connect = mysql_connect('127.0.0.1', 'root', '') or die('Could not connect: ' . mysql_error());
else 
	$connect = mysql_connect('127.0.0.1', 'root', 'bsw$$2009') or die('Could not connect: ' . mysql_error());
	
	
$bsw_bi=mysql_select_db('bsw_bi', $connect);
$bds=mysql_query("select * from bi_server where active=true");

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
		echo '========> ' . $bd['dbName'] . "\n";
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
		$items='select * from bi_dashboard order by id_item';
			
		$itemsr=mysql_query($items);
		$cnt=0;
		while ($item = mysql_fetch_array($itemsr,MYSQL_ASSOC))
		{
			$cnt++;	
			$table='xyz_' . str_pad($item['id_item'], 10, "0", STR_PAD_LEFT);		
			$createIndex = false;
			if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . $table . "'"))==1) 
			{
				$maxClock = mysql_query('select max(clock) as clock from ' . $table);
				$max=mysql_fetch_array($maxClock,MYSQL_ASSOC);
				$max=$max['clock'];
				if (!isset($max)) $max=0;
				$query  = 'insert into ' . $table . ' select clock,id_host,value,valid from bm_history where id_item=' . $item['id_item'] . ' and clock > ' . $max;
			}
			else {
				$query  = 'create table ' . $table . ' select clock,id_host,value,valid from bm_history where id_item=' . $item['id_item'];	
				$createIndex=true;
			}
			$result=mysql_query($query);
			$nr= mysql_affected_rows();
			echo round($cnt/(mysql_num_rows($itemsr))*100,2) . "% " . $table . " ";
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
	}
	else
	{
		 echo '========> ' . $bd['dbName'] . " skipped\n";
	}		
}
?>
