<?php 

if (isset($_GET['SETEXECUTE'])) {
	//One time execution
	if (isset($_GET['SETEXECUTE'])) {
		$SETEXECUTE = $_GET['SETEXECUTE'];
		$valid = $cmd->conexion->query("REPLACE INTO `Parametros` ( `nombre`, `descripcion`, `type`, `valor`, `visible`)
			VALUES
				( 'STATUS_EXECUTE_BI', 'runBI execution', 'int', '$SETEXECUTE', 'true')");
	}

	exit ;
}

$fileLogs = 'runBI2.'.$urlBase.'.log';

if(file_exists($fileLogs)) {
	$logsBSW = file_get_contents('runBI2.'.$urlBase.'.log', true);
	
	$html = nl2br(htmlspecialchars($logsBSW));
	$html = preg_replace('/\s\s+/', ' ', $html);
	$html = preg_replace('/\s(\w+:\/\/)(\S+)/', ' <a href="\\1\\2" target="_blank">\\1\\2</a>', $html);

	echo $html;
	
}
?>