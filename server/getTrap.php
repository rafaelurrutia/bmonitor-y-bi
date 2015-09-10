<?php

$SQLHOST='127.0.0.1';
$SQLUSER='bswtraps';
$SQLPASS='bswtraps321';
$SQLDB='bswtraps';
$SQLTBL='traps';

$stdin = fopen('php://stdin', 'r');

$trap_arr = array();

$i=0;
while ( $trap_line = trim(fgets($stdin,4096)) ) {

    $trap_key   = ereg_replace("([0-9\.]*)\ (.*)","\\1", $trap_line);
    $trap_value = ereg_replace("([0-9\.]*)\ (.*)","\\2", $trap_line);

    $trap_arr[$trap_key] = $trap_value;
    //syslog(LOG_INFO, "$i ==> $trap_key == $trap_value");

    $i++;
    if ($i == 10) {
        // prevent loop forever
        die();
    }
}

function errorLog($string,$exit = false) {
	error_log($string, 3, "/var/www/html/linksys/trap.log");
	if($exit) {
		exit;
	}
}

function oiSnmptrapHandle($trap_arr) {
    $_ret = '';

    // snmpTrapOID
    if ( isset($trap_arr['.1.3.6.1.6.3.18.1.3.0']) ) {
        $trap_version = "SNMPv1";
    } else {
        $trap_version = "SNMPv2";
    }
    $trap_address    =  ereg_replace("UDP:\[(.*)\].*", "\\1", $trap_arr['UDP:']);

    $trap_enterprise = $trap_arr['.1.3.6.1.6.3.1.1.4.3.0'];
    $trap_oid        = $trap_arr['.1.3.6.1.6.3.1.1.4.1.0'];

    $trap_details = '';

    // seperate snmptrap by device oid
    switch ($trap_oid) {
     case $trap_oid:
        if ( isset($trap_arr[$trap_oid]) ) {
            $trap_details = $trap_arr[$trap_oid];
             $_ret['detail'] = ereg_replace('"', '', $trap_details);
        }
        break;

     default:
        $_ret['detail'] = '';
        break;
    }

    $_ret['key'] = $trap_oid;
	$_ret['address'] = $trap_address;
	$_ret['version'] = $trap_version;

    // return
    return $_ret;
}

$trap_ret = oiSnmptrapHandle($trap_arr);

//Parser Traps

$conn = mysql_connect($SQLHOST, $SQLUSER, $SQLPASS);
if (!$conn) {
    errorLog('Could not connect: ' . mysql_error(),true);
}

$db_selected = mysql_select_db($SQLDB, $conn);
if (!$db_selected) {
	errorLog('Can\'t use '.$SQLDB.' : ' . mysql_error(),true);
}

// Separando detail traps 
$traps_array = explode('*', $trap_ret['detail']);

$string = print_r($traps_array,1);

errorLog($string);

if($traps_array[1] != 'DISP') {

	$insert_sql = "INSERT INTO `$SQLTBL` (`id_traps`, `FECHA_HORA`, `ENCABEZADO`, `RUT`, `NODO`, `CUADRANTE`, `HOST`, `GROUP`, `OID`, `TIPO`, `VALORESPERADO`, `VALOROBTENIDO`, `LOGIN`) VALUES ";
	
	$values_sql = "(NULL,NOW(),'$traps_array[0]','$traps_array[1]','$traps_array[2]','$traps_array[3]','$traps_array[5]','$traps_array[6]','$traps_array[7]','$traps_array[8]','$traps_array[9]','$traps_array[10]','$traps_array[11]');";
	
	$result = mysql_query($insert_sql.$values_sql);
	if (!$result) {
    	errorLog('Invalid query: ' . mysql_error(),true);
	}

} else {
	$traps_dips_array1 = explode('_', $traps_array[2]);
	$string = print_r($traps_dips_array1,1);
	errorLog($string);
	
	$insert_sql = "INSERT INTO `$SQLTBL` (`FECHA_HORA`, `ENCABEZADO`, `TRAPCRIT`, `RDB`, `RUT`, `NODO`, `CUADRANTE`, `COMUNA` , `HOST`, `DNS`, `GROUP`, `PLAN`, `OID`, `TIPO`, `VALORESPERADO`, `VALOROBTENIDO`, `LOGIN`) VALUES ";
	
	$values_sql = printf("(NOW(),%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
						$traps_array[0],
						$traps_dips_array1[0],
						$traps_dips_array1[1],
						$traps_dips_array1[2],
						$traps_dips_array1[3],
						$traps_dips_array1[4],
						$traps_dips_array1[5],
						'',
						$traps_dips_array1[6],
						'',
						$traps_dips_array1[7],
						$trap_ret['key'],
						$traps_array[1],
						'1',
						'0',
						$traps_array[3]
					);
	$result = mysql_query($insert_sql.$values_sql);
	if (!$result) {
    	errorLog('Invalid query: ' . mysql_error(),true);
	}	
}

mysql_close($conn);
?>