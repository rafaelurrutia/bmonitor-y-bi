<?php
/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  cli.split
 * @author   Carlos Lazcano <carlos@bsw.cl>
 * @license  http://www.baking.cl/license BSD Licence
 * @version  SVN: 1: index.phpl,v 1.0 2012-11-22 14:06:47 cweiske Exp $
 * @link     http://baking.cl
 */
header('Content-type: text/html; charset=UTF-8');

$SITE_PATH = realpath(dirname(__FILE__) .  '/../') . "/";

require $SITE_PATH.'core/startup.inc.php';
/**
 *  Class split
 */
class Split
{

    private $dbNameCore = 'bsw_bi';

    function __construct($cmd)
    {
        $this->conexion = $cmd->conexion;
        $this->logs = $cmd->logs;
    }

    public function validStart()
    {
        $file = $_SERVER["SCRIPT_NAME"];
        $status = shell_exec('ps -weafe | grep "'.$file.'"  | grep -v  grep | wc -l');

        if($status > 1) {
            $this->logs->error('Start NOK', null, 'logs_split');
            exit;
        } else {
            $this->logs->info('Start OK', null, 'logs_split');
        }
        
    }
    
    public function start()
    {
        $servers = $this->getServer();

        if ($servers !== false) {

            foreach ($servers as $key => $server) {

                $this->setTimeZone($server['timezone']);

                $this->logs->info("Start split history server name: ", $server['name'], 'logs_split');

                $tick = $this->getTick($server['idServer']);

                $this->getDataHistory((object)$server, $tick);

            }

        }
    }

    private function setTimeZone($timezone)
    {
        date_default_timezone_set($timezone);
        ini_set('date.timezone', $timezone);

        $now = new DateTime();
        $mins = $now->getOffset() / 60;

        $sgn = ($mins < 0 ? -1 : 1);
        $mins = abs($mins);
        $hrs = floor($mins / 60);
        $mins -= $hrs * 60;

        $offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);

        $this->conexion->query("SET time_zone = '$offset';",false,'logs_split');
    }

    public function getDataHistory($server, $tick = false)
    {
        $change = $this->conexion->changeDB($server->dbName,'logs_split');

        if ($change == false) {
            exit ;
        }

        $currentMonth = date("n");
		
        $currentYear = date("Y");

        $lastMonth = date("n") - 1;
       
        $startCurrentMonth = mktime(0, 0, 0, $currentMonth, 1, $currentYear);

        $startLastMonth = mktime(0, 0, 1, $currentMonth - 1, 1, $currentYear);

        if (file_exists("/tmp/split.csv")) {
            unlink("/tmp/split.csv");
        }
		
		$tableCreateResult = $this->createTableHistory($server->idServer, $currentYear, $currentMonth);

        if ($tick === false) {

            $loadHistorySQL = 'SELECT * FROM `' . $server->dbName . '`.`bm_history` WHERE `clock` >= ' . $startCurrentMonth . ' ORDER BY `clock` ASC
                             INTO OUTFILE "/tmp/split.csv"';

            $loadHistoryRESULT = $this->conexion->query($loadHistorySQL,false,'logs_split');

            $dbNameCore = $this->conexion->changeDB($this->dbNameCore,'logs_split');

            if ($dbNameCore) {
                
                if ($tableCreateResult == true) {

                    $insertHistoryRESULT = $this->conexion->query("LOAD DATA INFILE '/tmp/split.csv' 
                            INTO TABLE `bi_history_" . $server->idServer . "_" . $currentYear . "_" . $currentMonth . "`;",false,'logs_split');

                    if ($insertHistoryRESULT) {
                        $this->setTick($server->idServer, $currentYear, $currentMonth);
                    }
                }
            }

        } else {

            $clockFinishMonth = false;
            if ($tick->clock < $startCurrentMonth && $tick->clock >= $startLastMonth) {
                $validMonthSQL = "SELECT count(*) As Total FROM `" . $server->dbName . "`.`bm_history` WHERE `id_history` > '$tick->idHistory'  AND `clock` < $startCurrentMonth LIMIT 1;";
                $validMonthRESULT = $this->conexion->queryFetch($validMonthSQL);

                if ($validMonthRESULT) {
                    if ($validMonthRESULT[0]['Total'] == 0) {
                        $clockFinishMonth = true;
                    }
                }
            }

            if ($tick->clock >= $startCurrentMonth || $clockFinishMonth === true) {

                $loadHistorySQL = 'SELECT * FROM `' . $server->dbName . '`.`bm_history` WHERE `id_history` > ' . $tick->idHistory . ' 
                             INTO OUTFILE "/tmp/split.csv"';

                $loadHistoryRESULT = $this->conexion->query($loadHistorySQL,false,'logs_split');

                $dbNameCore = $this->conexion->changeDB($this->dbNameCore);

                if ($dbNameCore) {
                    $tableCreateResult = $this->createTableHistory($server->idServer, $currentYear, $currentMonth);
                    if ($tableCreateResult == true) {

                        $insertHistoryRESULT = $this->conexion->query("LOAD DATA INFILE '/tmp/split.csv' 
                            INTO TABLE `bi_history_" . $server->idServer . "_" . $currentYear . "_" . $currentMonth . "`;",false,'logs_split');

                        if ($insertHistoryRESULT) {
                            $this->setTick($server->idServer, $currentYear, $currentMonth);
                        }
                    }
                }

            } elseif ($tick->clock < $startCurrentMonth && $tick->clock >= $startLastMonth) {

                $this->logs->info("Start split , last month: $lastMonth , clock ", $tick->clock . "  " . $startCurrentMonth);

                $loadHistorySQL = 'SELECT * FROM `' . $server->dbName . '`.`bm_history` WHERE `id_history` > ' . $tick->idHistory . '  AND
                            `clock` < ' . $startCurrentMonth . '
                             INTO OUTFILE "/tmp/split.csv"';

                $loadHistoryRESULT = $this->conexion->query($loadHistorySQL,false,'logs_split');

                $dbNameCore = $this->conexion->changeDB($this->dbNameCore);

                if ($dbNameCore) {
                    $tableCreateResult = $this->createTableHistory($server->idServer, $currentYear, $lastMonth);
                    if ($tableCreateResult == true) {

                        $insertHistoryRESULT = $this->conexion->query("LOAD DATA INFILE '/tmp/split.csv' 
                            INTO TABLE `bi_history_" . $server->idServer . "_" . $currentYear . "_" . $lastMonth . "`;",false,'logs_split');

                        if ($insertHistoryRESULT) {
                            $this->setTick($server->idServer, $currentYear, $lastMonth);
                        }
                    }
                }

            }

        }
    }

    // Function Get Server

    public function getServer()
    {
        $getServerSQL = 'SELECT `idServer`, `name`, `domain`, `dbName`, `timezone` FROM `' . $this->dbNameCore . '`.`bi_server`;';
        $getServerRESULT = $this->conexion->queryFetch($getServerSQL);

        if ($getServerRESULT) {
            return $getServerRESULT;
        } else {
            return false;
        }
    }

    // Function Get ID Tick

    public function getTick($idServer)
    {
        $getTickSQL = "SELECT * FROM `" . $this->dbNameCore . "`.`bi_tick` WHERE `idServer` = $idServer ORDER BY `idTick` DESC LIMIT 1";
        $getTickRESULT = $this->conexion->queryFetch($getTickSQL);

        if ($getTickRESULT) {
            return (object)$getTickRESULT[0];
        } else {
            return false;
        }
    }

    // Function Set ID Tick

    public function setTick($idServer, $year, $month)
    {
        $getTickSQL = "SELECT
                        IFNULL(MIN(`id_history`), 0)  as minIDHistory,
                        IFNULL(MAX(`clock`),0) as minClockHistory,
                        IFNULL(MAX(`id_history`),0) as maxIDHistory,
                        IFNULL(MAX(`clock`),0) as maxClockHistory 
                    FROM `bi_history_" . $idServer . "_" . $year . "_" . $month . "`";
        $getTickRESULT = $this->conexion->queryFetch($getTickSQL);

        if ($getTickRESULT) {
            $history = (object)$getTickRESULT[0];
            $insertTickSQL = "INSERT INTO `bi_tick` ( `idServer`, `idHistory`, `clock`, `date`, `type`)
                    VALUES
                        ( $idServer, $history->minIDHistory, $history->minClockHistory, '$year-$month', 'start'),
                        ( $idServer, $history->maxIDHistory, $history->maxClockHistory, '$year-$month', 'end') ON DUPLICATE KEY UPDATE `idHistory`=VALUES(`idHistory`) , `clock`=VALUES(`clock`)";
            $insertTickRESULT = $this->conexion->query($insertTickSQL,false,'logs_split');

            if ($insertTickRESULT) {
                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    // Function Create Table History

    public function createTableHistory($idServer, $year, $month)
    {
        $createTableSQL = "CREATE TABLE  IF NOT EXISTS  `" . $this->dbNameCore . "`.`bi_history_" . $idServer . "_" . $year . "_" . $month . "` (
              `id_history` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `id_item` int(11) unsigned NOT NULL DEFAULT '0',
              `id_host` int(11) unsigned NOT NULL,
              `clock` int(11) unsigned NOT NULL DEFAULT '0',
              `value` double(16,4) unsigned NOT NULL DEFAULT '0.0000',
              `valid` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id_history`),
              	KEY `Dual` (`id_host`,`id_item`),
                KEY `id_item` (`id_item`),
  				KEY `id_host` (`id_host`),
  				KEY `clock` (`clock`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $createTableRESULT = $this->conexion->query($createTableSQL,false,'logs_split');
		
		$month = $month+1;

        $createTableSQL = "CREATE TABLE  IF NOT EXISTS `" . $this->dbNameCore . "`.`bi_history_" . $idServer . "_" . $year . "_" . $month . "` (
              `id_history` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `id_item` int(11) unsigned NOT NULL DEFAULT '0',
              `id_host` int(11) unsigned NOT NULL,
              `clock` int(11) unsigned NOT NULL DEFAULT '0',
              `value` double(16,4) unsigned NOT NULL DEFAULT '0.0000',
              `valid` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id_history`),
              	KEY `Dual` (`id_host`,`id_item`),
                KEY `id_item` (`id_item`),
  				KEY `id_host` (`id_host`),
  				KEY `clock` (`clock`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $createTableRESULT = $this->conexion->query($createTableSQL,false,'logs_split');

        
        if ($createTableRESULT) {
            return true;
        } else {
            return false;
        }
    }

}

$cmd = new Control(true, 'core.bi.node1.baking.cl',true);

$cmd->parametro->remove('STDOUT');

$cmd->parametro->set('STDOUT', true);

//Start Split
$split = new Split($cmd);

$split->validStart();

$split->start();
