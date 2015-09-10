<?php
//este script es idem al DBsplit.php para BI, pero esta nueva versión tambien genera   
//data en las tablas xyz_0000item que corresponden a los items de bmonitor 
//que es una mejora a bmonitor2.5 detalle en ticket007 o BUGS022
//Actualización del 2015/03 
//este es un parche momentaneo ya que solo se ejecuta en esta maquina y a las bases de datos vtr.cl.qoe y claro.pe.qoe

if (php_uname("s") == "Darwin") 
	$connect = mysql_connect('127.0.0.1', 'root', '') or die('Could not connect: ' . mysql_error());
else 
	$connect = mysql_connect('127.0.0.1', 'root', 'bsw$$2009') or die('Could not connect: ' . mysql_error());
	
$bsw_bi=mysql_select_db('bsw_bi', $connect);  
//$bds=mysql_query("select * from bi_server where active=true");
$bds= array('bmonitor','bmonitorTDP'); //'claro.pe.qoe','vtr.cl.qoe');  

//obtener fecha inicial de carga de data.
$monthNow = date('n'); 
$dateNow = date('Y-m-j');
$mesesAntes= date( "Y-m-j", strtotime( "$dateNow -3 month" ) ); // PHP: 3 meses antes    
$fechaInicioCargaData= date("".$mesesAntes);
$clockInicio = strtotime ('-'.$monthNow.' months' , strtotime ( $fechaInicioCargaData ) ) ;
//echo "\n--->fecha hoy es: $dateNow, ";
//echo "\n fecha Antes: $fechaInicioCargaData, --> fechantes en clock: $clockInicio";   
$cargaInicial=false;
//$bd['dbName'][0]='bmonitor'; 
//$bd['dbName'][1]='vtr.cl.qoe';
//while ($bd = mysql_fetch_array($bds,MYSQL_ASSOC))
foreach ($bds as $key => $bd) 
{
	$go=false;
	if (count($argv) > 1) {
		if($argv[0]="carga" && $argv[1]="inicial"){
			$cargaInicial=true;
			$go=true;
			echo "\n arg[0]=$argv[0];  -->  arv[1]=$argv[1],    entre a  CARGA INICIAL \n";
		}else {
			if (array_search( $bd/*['dbName']*/, $argv) > 0) $go=true;
		}
  	}
	else
	  $go=true;
	if ($go) {
		$bool = mysql_select_db($bd/*['dbName']*/, $connect);
		echo '\n========> ' . $bd/*['dbName']*/ . "\n";
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

//---------- DBsplit para BI ----------------------------------------------------------------- 
		$items='select * from bi_dashboard order by id_item';			
		$itemsr=mysql_query($items);
		$cnt=0;
		echo "\n -----> ITEMS for BI <------\n";
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
				$query  = 'insert into ' . $table . ' select clock,id_host,value,valid from bm_history 
				where id_item=' . $item['id_item'] . ' and clock > ' . $max;
			}
			else {
				$query  = 'create table ' . $table . ' select clock,id_host,value,valid from bm_history 
				where id_item=' . $item['id_item'];	
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
//---------- FIN DBsplit para BI -----------------------------------------------------------------
 
//.......... DBsplit para bmonitor actualización de tablas xyz_00000item ......................... 
//.......... actualización 2015-03 
		
 		$items='SELECT PV.id_monitor as id_item from bm_profiles_category PC 
 		LEFT JOIN bm_profiles_values PV ON PV.id_category=PC.id_category WHERE PC.`status`=TRUE AND PV.id_monitor>0';			
		$itemsr=mysql_query($items);
		$cnt=0;
		echo "\n -----> ITEMS for Bmonitor <------\n";
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
				if($cargaInicial==false){
					if (!isset($max)) $max=0;
					$query  = 'insert into ' . $table . ' select clock,id_host,value,valid from bm_history 
					where id_item=' . $item['id_item'] . ' and clock > ' . $max;
				}else{
					$query  = 'insert into ' . $table . ' select clock,id_host,value,valid from bm_history 
					where id_item=' . $item['id_item'] . ' and clock > ' . $clockInicio; 
				}
			}
			else {
				if($cargaInicial==false){	
					$query  = 'create table ' . $table . ' select clock,id_host,value,valid from bm_history 
					where id_item=' . $item['id_item'];	
					$createIndex=true;
				}else{
					$query  = 'create table ' . $table . ' select clock,id_host,value,valid from bm_history 
					where id_item=' . $item['id_item'] . ' and clock > ' . $clockInicio;
					$createIndex=true;					
				}
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
//.......... FIN DBsplit para bmonitor ...............................................................  
 }else{
		 echo '========> ' . $bd/*['dbName']*/ . " skipped\n";
	}		
}
 
?>
