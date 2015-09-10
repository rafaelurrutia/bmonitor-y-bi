<?php
/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 * envia mail de sondas donde reporta dondas UP y detalle de sondas Down. 
 */
header('Content-type: text/html; charset=UTF-8');

$SITE_PATH = realpath(dirname(__FILE__) . '/../') . "/";
//echo "\nsite patch ".$SITE_PATH;
require $SITE_PATH . 'core/startup.inc.php';

/**
 *  envio de mail de sondas qoE
 */

/**
 *
 */
class Poller {
        private $dbNameCore = 'bsw_bi';
        private $delay_box = array();

        function __construct($cmd) {
                $this->conexion = $cmd->conexion;
                $this->logs = $cmd->logs;
                $this->basic = $cmd->basic;
                $this->parametro = $cmd->parametro;
                $this->language = $cmd->language;
        }

        public function validStart() {
                $file = $_SERVER["SCRIPT_NAME"];
                $status = shell_exec('ps -weafe | grep "' . $file . '"  | grep -v  grep | wc -l');

                if ($status > 1) {
                        $this->logs->error('Start NOK', null, 'logs_poller');
                        exit ;
                } else {
                        $this->logs->info('Start OK', null, 'logs_poller');
                        //echo "\nOK";
                }
                //echo "\n fin valid";
        }

/*      public function start() {
                $servers = $this->getServer();

                if ($servers !== false) {
                        foreach ($servers as $key => $server) {

                                $this->setTimeZone($server['timezone']);

                                $this->logs->info("Start poller server name: ", $server['name'], 'logs_poller');

                                $change = $this->conexion->changeDB($server['dbName']);

                                if ($change == false) {
                                        $this->logs->error("Change Database", $change, 'logs_poller');
                                        continue;
                                }

                                ///Cargar Funciones
                                //$this->startPoller((object)$server);
                                //$this->alertMonitor((object)$server);
                        }

                }
        }
*/
        private function setTimeZone($timezone) {
                date_default_timezone_set($timezone);
                ini_set('date.timezone', $timezone);

                $now = new DateTime();
                $mins = $now->getOffset() / 60;

                $sgn = ($mins < 0 ? -1 : 1);
                $mins = abs($mins);
                $hrs = floor($mins / 60);
                $mins -= $hrs * 60;

                $offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);

                $this->conexion->query("SET time_zone = '$offset';");
        }

        // Function Get Server

        public function getServer() {
                $getServerSQL = 'SELECT `idServer`, `name`, `domain`, `dbName`, `timezone`, `mailAlert` FROM `' . $this->dbNameCore . '`.`bi_server` WHERE `active` = \'true\';';
                echo "\n1 --> ".$getServerSQL;
                $getServerRESULT = $this->conexion->queryFetch($getServerSQL);

                if ($getServerRESULT) {
                        echo "\n ok get server); ";
                        return $getServerRESULT;
                } else {
                        return false;
                        echo "\n false no hay getserver. ";
                }
                echo "\n--> ".$getServerSQL;
        }



        public function sendMail() {
                //echo "\n entre a sendMail";
                $servers = $this->getServer();
/*              $getServerSQL = 'SELECT `idServer`, `name`, `domain`, `dbName`, `timezone`, `mailAlert` FROM `' . $this->dbNameCore . '`.`bi_server` WHERE `active` = \'true\';';
                echo "\n1 --> ".$getServerSQL;
                $getServerRESULT = $this->conexion->queryFetch($getServerSQL);

                if ($getServerRESULT) {
                        echo "\n ok get server); ";
                        $servers= $getServerRESULT;
                } else {
                        $servers= false;
                        echo "\n false no hay getserver. ";
                }
                echo "\n--> ".$getServerSQL;

*/
                echo "\n paso servers".$servers[0]['dbName'];

                if ($servers !== false) {
                        foreach ($servers as $keyServer => $server) {

                                $this->setTimeZone($server['timezone']);

                                $this->logs->info("Start poller server name: ", $server['name'], 'logs_poller');

                                ///Send mail for server
                                $change = $this->conexion->changeDB($server['dbName']);

                                if ($change == false) {
                                        continue;
                                }

                                $getServerSQL = 'SELECT NOW() as "fechahora", H.`id_host`, H.`groupid`,G.`name`, H.`host`, IF(IP.`lastclock` > ( UNIX_TIMESTAMP(NOW()) - 7000 ),1,0) as "STATUS_SONDA",
                                                                                        IF(IP.`lastclock` IS NULL , "1970-01-01 00:00:00" ,FROM_UNIXTIME(IP.`lastclock`))  as "updateSONDA", H.`codigosonda` as "dns"
                                                                                FROM `bm_item_profile` IP
                                                                                        LEFT JOIN `bm_host` H ON H.`id_host`=IP.`id_host`
                                                                                        LEFT JOIN `bm_host_groups` G ON G.`groupid`=H.`groupid`
                                                                                WHERE IP.`id_item` =4 AND H.`status`=1  AND  H.`borrado` = 0 GROUP BY H.`id_host` ORDER BY H.`id_host`';

                                $getServerRESULT = $this->conexion->queryFetch($getServerSQL);

                                if ($getServerRESULT) {

                                        //Count Agent
                                        $down = 0;
                                        $up = 0;
                                        $agents = array();
                                        foreach ($getServerRESULT as $keyAlert => $alerta) {
                                                if ($alerta["STATUS_SONDA"] == 0) {
                                                        $agents[$alerta["id_host"]] = false;
                                                        $down++;
                                                } else {
                                                        $agents[$alerta['id_host']] = true;
                                                        $up++;
                                                }
                                        }

                                        $site = $this->parametro->get("SITE", $server['name']);

                                        //Validando Cambios

                                        $fileName = SITE_PATH . 'tmp/' . md5($server['name']) . '.mail';

                                        $diffAlert = array();
                                        $arryPrevious = array();
                                        $hashPrevious = '';

                                        if (file_exists($fileName)) {

                                                $fp = fopen($fileName, "r");

                                                if ($fp) {
                                                        if (filesize($fileName) > 0) {
                                                                $hashPreviousContainer = trim(@fread($fp, filesize($fileName)));
                                                                list($hashPrevious, $statusPrevious) = explode(';', $hashPreviousContainer);
                                                                $hashPrevious = trim($hashPrevious);
                                                                //Comparando para colorear diferencia
                                                                $arryPrevious = json_decode($statusPrevious, true, JSON_NUMERIC_CHECK);

                                                        }
                                                }

                                                fclose($fp);
                                        }

                                        foreach ($agents as $keyAgent => $agent) {
                                                if ((isset($arryPrevious[$keyAgent])) && ($agent !== $arryPrevious[$keyAgent])) {
                                                        $diffAlert[$keyAgent] = $agent;
                                                }
                                        }

                                        $header = '<html>
                                                        <head>
                                                        </head><body><style type="text/css">
                                                                tr.rowB:hover { background: #d9ebf5 !important; border-left: 1px solid #eef8ff !important; border-bottom: 1px dotted #a8d8eb !important; }
                                                                tr.rowA:hover { background: #d9ebf5 !important; border-left: 1px solid #eef8ff !important; border-bottom: 1px dotted #a8d8eb !important; }
                                                                ></style>';

                                        $tableCount = '<div style="border: 1px solid bisque; padding: 18px;">Smart Agents: <font color="green">' . $up . ' OK </font>and <font color="red">' . $down . ' DOWN </font></div><br>';

                                        $table = '<table class="ui-grid-content ui-widget-content">';

                                        $tableHeader = '<tr>
                                                <th style="min-width: 60px!important;text-align: left" class="ui-state-default">' . $this->language->GROUPS . '</th>
                                                <th style="min-width: 150px!important;text-align: left" class="ui-state-default">' . $this->language->EQUIPO_PLANTILLA . '</th>
                                                <th style="min-width: 100px!important;text-align: left" class="ui-state-default">' . $this->language->PROBLEM . '</th>
                                                <th class="ui-state-default">' . $this->language->AGENT_CODE . '</th>
                                                <th style="min-width: 100px!important;text-align: left" class="ui-state-default">' . $this->language->LAST_DATE . '</th></tr>';

                                        $tableHeaderDiff = '<tr>
                                                <th style="min-width: 60px!important;text-align: left" class="ui-state-default">' . $this->language->STATUS . '</th>
                                                <th style="min-width: 150px!important;text-align: left" class="ui-state-default">' . $this->language->EQUIPO_PLANTILLA . '</th>
                                                <th style="min-width: 100px!important;text-align: left" class="ui-state-default">' . $this->language->PROBLEM . '</th></tr>';

                                        //$portlet_table .= '<th class="ui-state-default">' . $this->language->LAST_CONNECTION . '</th>';

                                        $portlet_table = $header . $tableCount . '<div style="border: 1px solid #2694e8;padding: 4px;width: 509px;background-color: aliceblue;color: red;text-align: center;">Down Agents</div>' . $table . $tableHeader;

                                        $class = 'class="rowA" style="background-color: #eeffee;" bgcolor="#eeffee"';
                                        $diffTable = '';
                                        $diffTable = '';
                                        foreach ($getServerRESULT as $key => $alerta) {

                                                if ($alerta["STATUS_SONDA"] == 0) {
                                                        $tt = $this->language->DASHBOARD_AGENT_NOT_REPORT;
                                                } else {
                                                        $tt = "Data received";
                                                }

                                                $trTable = '<tr ' . $class . '><td class="ui-grid-content">' . $alerta["name"] . '</td><td class="ui-grid-content">' . $alerta["host"] . '</td>';
                                                $trTable .= '<td class="ui-grid-content">' . $tt . '</td>';
                                                // $portlet_table .= '<td class="ui-grid-content">' . $alerta["updateSONDA"] . '</td>';
                                                $trTable .= '<td class="ui-grid-content">' . $alerta["dns"] . '</td>';
                                                $trTable .= '<td class="ui-grid-content">' . $alerta["updateSONDA"] . '</td></tr>';

                                                if ($class == 'class="rowA" style="background-color: #eeffee;" bgcolor="#eeffee"') {
                                                        $class = 'class="rowB" style="background-color: #ddffdd;" bgcolor="#ddffdd"';
                                                } else {
                                                        $class = 'class="rowA" style="background-color: #eeffee;" bgcolor="#eeffee"';
                                                }

                                                if ($alerta["STATUS_SONDA"] == 0) {
                                                        $portlet_table .= $trTable;
                                                }

                                                if (isset($diffAlert[$alerta['id_host']])) {

                                                        if ($diffAlert[$alerta['id_host']] == true) {
                                                                $trTableFilter = '<tr ' . $class . '><td class="ui-grid-content"><font color="green">OK</font></td><td class="ui-grid-content">' . $alerta["host"] . '</td>';
                                                                $trTableFilter .= '<td class="ui-grid-content">' . $tt . '</td><tr>';
                                                        } else {
                                                                $trTableFilter = '<tr ' . $class . '><td class="ui-grid-content"><font color="red">DOWN</font></td><td class="ui-grid-content">' . $alerta["host"] . '</td>';
                                                                $trTableFilter .= '<td class="ui-grid-content">' . $tt . '</td><tr>';
                                                        }

                                                        $diffTable .= $trTableFilter;

                                                }

                                        }

                                        $portlet_table .= '</table>';

                                        if (count($diffAlert) > 0) {
                                                $portlet_table .= '<br><div style="border: 1px solid #2694e8;padding: 4px;width: 509px;background-color: aliceblue;text-align: center;">Changes compared to the previous state</div><br>' . $table;
                                                $portlet_table .= $tableHeaderDiff;
                                                $portlet_table .= $diffTable;
                                                $portlet_table .= '</table>';
                                        }

                                        $portlet_table .= '</body></html>';

                                        $hash = md5(serialize($agents));

                                        $fp = fopen($fileName, "w");
                                        if ($fp) {
                                                $agentsJson = json_encode($agents, JSON_NUMERIC_CHECK);
                                                if (json_last_error() !== JSON_ERROR_NONE) {
                                                        //error_log(print_r($agents,true));
                                                }
                                                fwrite($fp, $hash . ";" . $agentsJson);
                                                fclose($fp);
                                        }

                                        if ($hashPrevious !== $hash) {
                                                echo "Alert";
                                                $this->basic->mail("roger.nunez@bsw.cl", "Soporte BSW", "soporte@bsw.cl", "QoE Smart Agent Information " . $site, $portlet_table);
                                                //Configuration DB bsw_bi
                                                if (!is_null($server['mailAlert'])) {
                                                        $this->basic->mail($server['mailAlert'], "Soporte BSW", "soporte@bsw.cl", "QoE Smart Agent Information " . $site, $portlet_table);
                                                }
                                        }

                                } else {
                                        continue;
                                }
                        }

                }
        }


}

$cmd = new Control(true, 'core.bi.node1.baking.cl', true);
/*
 $cmd->parametro->remove('STDOUT');
 $cmd->parametro->set('STDOUT', true);
 $cmd->parametro->remove('DEBUG');
 $cmd->parametro->set('DEBUG', true);
 */

//Start Split
$poller = new Poller($cmd);
$poller->validStart();
//$poller->start();
$poller->sendMail();
?>
                                                         