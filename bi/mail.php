<?php
$SITE_PATH = realpath(dirname(__FILE__) . '/../') . "/";
require $SITE_PATH . 'core/startup.inc.php';

function linkPage($qoe, $connect) {
	global $SITE_PATH;

	switch ($qoe) {
		case "mediacom.us.qoe.baking.cl" :
			$db = "mediacom.us.qoe";
			break;
		case "cablevision.mx.qoe.baking.cl" :
			$db = "cablevision.qoe";
			break;
		case "cablevision.ar.qoe.baking.cl" :
			$db = "cablevision.ar.qoe";
			break;
		case "movistar.cl.qoe.baking.cl" :
			$db = "qosmovistar";
			break;
		case "vtr.cl.qoe.baking.cl" :
			$db = "vtr.cl.qoe";
			break;
		case "amx.pe.qoe.baking.cl" :
			$db = "amx.pe.qoe";
			break;
		case "amx.co.qoe.baking.cl" :
			$db = "bmonitor";
			break;
		case "amx.cl.qoe.baking.cl" :
			$db = "bmonitorclaro";
			break;
		case "une.co.qoe.baking.cl" :
			$db = "une.co.qoe";
			break;
		case "multimedios.mx.qoe.baking.cl" :
			$db = "multimedios.mx.qoe";
			break;
		case "amx.ar.qoe.baking.cl" :
			$db = "bmonitorclaroba";
			break;
		case "tigo.gt.qoe.baking.cl" :
			$db = "tigo.gt.qoe";
			break;
		case "claro.pe.qoe.baking.cl" :
			$db = "claro.pe.qoe";
			break;
		case "claro.co.qoe.baking.cl" :
			$db = "claro.co.qoe";
			break;
	}
	echo $db . "\n";
	$cmd = new Control(true, 'core.bi.node1.baking.cl');

	$bool = mysql_select_db($db, $connect);
	$qoffset = 'select valor from Parametros where nombre="TIMEZONE_OFFSET"';
	$qresult = mysql_query($qoffset);
	if (mysql_num_rows($qresult) == 1)
		$offset = 0;
	else
		$offset = 0;
	$qoffset = 'select valor from Parametros where nombre="SITE"';
	$qresult = mysql_query($qoffset);
	if (mysql_num_rows($qresult) == 1)
		$site = mysql_result($qresult, 0);
	else
		$site = $qoe;
	$qoffset = 'select valor from Parametros where nombre="TIMEZONE"';
	$qresult = mysql_query($qoffset);
	if (mysql_num_rows($qresult) == 1)
		mysql_query("SET time_zone = '" . mysql_result($qresult, 0) . "'");

	$rs = mysql_query("select count(*) as cnt from bm_host where borrado=0 and status=1");
	$cnt = mysql_result($rs, 0);

	/*$rs=mysql_query('select codigosonda,bm_host.host,from_unixtime(max(lastclock+' . $offset . ')) as
	 * lastclock,round((unix_timestamp(now())-max(lastclock+' . $offset . '))/60,0) as diff from bm_item_profile,bm_host
	 where bm_item_profile.id_host=bm_host.id_host and borrado=0 and bm_host.status=1 and lastclock is not null
	 group by bm_host.id_host
	 order by lastclock,codigosonda,bm_host.host desc');*/

	$rs = mysql_query("SELECT `dns` as 'codigosonda', `host`,`updateSONDA`,`STATUS_SONDA`, DATEDIFF(NOW(),`updateSONDA`) as 'diff' 
							FROM `bm_availability` ORDER BY  `updateSONDA`,`dns`,`host` DESC");

	$qn = 0;
	$qni = 0;
	$list = "";
	while ($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {

		$last = $row['updateSONDA'];
		$host = $row['host'];
		$diff = $row['diff'];
		$codigosonda = $row["codigosonda"];
		if ($host != $codigosonda)
			$host .= "/" . $codigosonda;

		if ($diff > 40) {
			$color = "red";
			if ($diff >= 40)
				$color = "#FF6600";
			if ($diff >= 60)
				$color = "black";
			$list = $list . '<font color="' . $color . '">' . $last . '      ' . $host . "</font><br>";
			$qn = $qn + 1;
		} else {
			$qni = $qni + 1;
		}
	}
	$rs = mysql_query('select codigosonda,bm_host.host from bm_host
            where id_host not in (select id_host from bm_item_profile where lastclock is not null)  and borrado=0 and bm_host.status=1
            order by host');
	while ($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {

		$last = '0000-00-00 00:00:00';
		$host = $row['host'];
		$codigosonda = $row["codigosonda"];
		if ($host != $codigosonda)
			$host .= "/" . $codigosonda;
		$color = "darkblue";
		$list = $list . $last . '     ' . $host . "<br>";
		$qn = $qn + 1;
	}
	
	$list = 'Smart Agents: <font color="green">' . $qni . ' OK </font>and <font color="red">' . $qn . ' DOWN </font><br>' . $list;

	if (file_exists($SITE_PATH . "bi/status/" . $qoe)) {
		$fp = fopen($SITE_PATH . "bi/status/" . $qoe, 'r');
		$content = fread($fp, filesize($SITE_PATH . "bi/status/" . $qoe));
		fclose($fp);
	} else {
		$content = "";
	}

	$fp = fopen($SITE_PATH . "bi/status/" . $qoe, 'w');
	fwrite($fp, $list);
	fclose($fp);

	if ($content != $list) {
		echo "sent for " . $qoe . "\n";
		$cmd->basic->mail("rodrigo@bsw.cl, carlos@bsw.cl, andres@bsw.cl", "Soporte BSW", "soporte@bsw.cl", "QoE Smart Agent Information " . $site, $list);
		if ($qoe == 'vtr.cl.qoe.baking.cl') {
			$cmd->basic->mail("alicia.lobos@vtr.cl", "Soporte BSW", "soporte@bsw.cl", "QoE Smart Agent Information " . $site, $list);
		} elseif ($qoe == 'claro.co.qoe.baking.cl') {
			$cmd->basic->mail("gestionredacceso@claro.com.co", "Soporte BSW", "soporte@bsw.cl", "QoE Smart Agent Information " . $site, $list);
		}
	}
}

function Go() {
	$host = gethostname();
	$ip = gethostbyname($host);
	echo "ip:" . $ip . " " . $host . "\n";
	$connect = mysql_connect('127.0.0.1', 'root', 'bsw$$2009') or die('Could not connect: ' . mysql_error());

	//linkPage('amx.ar.qoe.baking.cl',$connect);
	linkPage('vtr.cl.qoe.baking.cl', $connect);
	linkPage('claro.pe.qoe.baking.cl', $connect);
	linkPage('claro.co.qoe.baking.cl', $connect);
	mysql_close();
	/*
	 linkPage('amx.cl.qoe.baking.cl',$connect);
	 linkPage('amx.co.qoe.baking.cl',$connect);
	 linkPage('amx.pe.qoe.baking.cl',$connect);
	 linkPage('cablevision.mx.qoe.baking.cl',$connect);
	 linkPage('cablevision.ar.qoe.baking.cl',$connect);
	 linkPage('mediacom.us.qoe.baking.cl',$connect);
	 linkPage('tigo.gt.qoe.baking.cl',$connect);
	 linkPage('multimedios.mx.qoe.baking.cl',$connect);
	 linkPage('une.co.qoe.baking.cl',$connect);
	 linkPage('movistar.cl.qoe.baking.cl',$connect);

	 *
	 */

}

Go();
?>
