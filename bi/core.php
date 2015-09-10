<?php
/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  bi.core
 * @license  http://www.baking.cl/license BSD Licence
 * @version  SVN: 1: index.phpl,v 1.0 2012-11-22 14:06:47 cweiske Exp $
 * @link     http://baking.cl
 */
header('Content-type: text/html; charset=UTF-8');

$SITE_PATH = realpath(dirname(__FILE__) .  '/../') . "/";
define('SITEPATH', $SITE_PATH);

class Connect {

	private $connect;
	private $dbNameCore = 'bsw_bi';
  	private $intererror = array(
            "NOTWRITABLE" => "El archivo de logs seleccionado no se puede escribir",
            "NOFILESETTING" => "Configuracion incorrecta!",
            "FILEWRITINGERROR" => "HO, ocurrio un error al escribir"
	);
	
	private $affected_rows = 0;

	function __construct($host, $user, $pass, $dbname = 'bsw_bi') {
		$this->connect = new mysqli($host, $user, $pass, $dbname);
		if ($this->connect->connect_errno) {
			echo "Connect failed: (" . $this->connect->connect_errno . ") " . $this->connect->connect_error;
			exit ;
		}
		echo $this->connect->host_info . "\n";
		echo $dbname . "\n--------------------------------------------------------------------\n";
	}

	public function changeDB($name)
	{
		$change = $this->connect->select_db($name);
		if ($result = $this->connect->query("SELECT DATABASE()")) {
		    $row = $result->fetch_row();
		    printf("Default database is %s.\n", $row[0]);
		    $result->close();
		}
		
		if($change !== false) {
			return true;
		} else {
			return false;
		}
		
	}

	public function validStart() {
		$file = $_SERVER["SCRIPT_NAME"];
		$status = shell_exec('ps -weafe | grep "' . $file . '"  | grep -v  grep | wc -l');

		if ($status > 1) {
			$this->error('Start NOK', null, 'logs_bi');
			exit ;
		} else {
			$this->info('Start OK', null, 'logs_bi');
		}
	}
	
	public function getAffectedRows()
	{
		return $this->affected_rows;
	}
		
	public function query($sql, $exit = false, $logs = "Error:") {
		$result = $this->connect->query($sql);

		if ($result) {
			$this->affected_rows = $this->connect->affected_rows;
			return $result;
		} else {
			printf($logs . "  %s\n", $this->connect->error);
			printf($logs . "  %s\n", $sql);
			if ($exit) {
				exit ;
			}
			return false;
		}
	}

	public function queryFetch($sql, $exit = false, $logs = "Error:") {
		$result = $this->connect->query($sql);

		if ($result) {

			$rows = array();
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$rows[] = $row;
			}

			return $rows;
		} else {
			printf($logs . "  %s\n", $this->connect->error);
			if ($exit) {
				exit ;
			}
			return false;
		}
	}

	public function close() {
		mysqli_close($this->connect);
	}

	public function setTimeZone($timezone) {
		date_default_timezone_set($timezone);
		ini_set('date.timezone', $timezone);

		$now = new DateTime();
		$mins = $now->getOffset() / 60;

		$sgn = ($mins < 0 ? -1 : 1);
		$mins = abs($mins);
		$hrs = floor($mins / 60);
		$mins -= $hrs * 60;

		$offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);

		$this->query("SET time_zone = '$offset';");
		
		echo date("Y-m-d H:i:s") . " " . 'Time Zone:' . $timezone . "\n";
	}

	public function timeStart() {
		$this->timeStart = microtime(true);
	}

	public function timeEnd() {
		$time_end = microtime(true);
		$time = $time_end - $this->timeStart;
		$time = round($time, 3);
		unset($this->timeStart);
		return $time;
	}

    public function getServer()
    {
        $getServerSQL = 'SELECT `idServer`, `name`, `domain`, `dbName`, `timezone`,  `backto` FROM `' . $this->dbNameCore . '`.`bi_server`;';
        $getServerRESULT = $this->queryFetch($getServerSQL);

        if ($getServerRESULT) {
            return $getServerRESULT;	
        } else {
            return false;
        }
    }

    private function isWritable($archivo)
    {

        $archivo_def = "/tmp/logBi";

        $create_file_is_not_exist = @fopen($archivo, "a");

        if (is_writable($archivo)) {
            return true;
        } elseif (is_writable($archivo_def)) {
            $fp = fopen($archivo_def, "a");
            if ($fp != null) {
                fwrite($fp, $this->intererror['NOTWRITABLE'] . " : " . $archivo . "\n");
                fclose($fp);
            }
            return false;
        } else {
            error_log($this->intererror['NOTWRITABLE'] . " : " . $archivo . "\n");
            return false;
        }
    }
	
    private function logger($nivel, $texto, $extra = null)
    {

        $archivo_def = "/tmp/logBi";

        $archivo = SITEPATH."logs/bi". ".log." . date("Y-m-d");
       
        $FILE = $this->isWritable($archivo);
        if (!$FILE) {
            $archivo = $archivo_def;
        }

        if (!isset($this->InitTime)) {
            $this->InitTime = time();
        }

        list($usec, $sec) = explode(" ", microtime());

        $usec = substr($usec, 2);

        $extraDump = "";

        if ($extra != null) {
            $extraDump = print_r($extra, true);
        }

        $getIP = $this->getIP();

        $ip = "";

        if (isset($getIP)) {
            $ip = $getIP . " ";
        }

        if (isset($_SERVER["REMOTE_USER"])) {
            $ip .= $_SERVER["REMOTE_USER"] . " ";
        }

        $linea = date("Y-m-d H:i:s") . "." . $usec . " " . (time() - $this->InitTime) . " [" . $this->getpid() . "] " . $ip . " " . $nivel . " " . $texto . (strlen($extraDump) > 0 ? $extraDump : "") . "\n";

        $fp = fopen($archivo, "a");
        if ($fp != null) {
            fwrite($fp, $linea);
            fclose($fp);
        }
    }

    public function info($msg, $data = false)
    {
        $this->logger("INFO", $msg, $data);
    }

    public function debug($msg, $data = false)
    {
    	$this->logger("DEBUG", $msg, $data);
    }

    public function warning($msg, $data = false)
    {
        $this->logger("WARNING", $msg, $data);
    }

    public function error($msg, $data = false)
    {
        $this->logger("ERROR", $msg, $data);
    }
	
    public function getIP() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = trim($ip);
                    // attempt to validate IP
                    if ($this->validateIP($ip)) {
                        return $ip;
                    }
                }
            }
        }
     
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "NOTIP";
    }
	
    private function validateIP($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        return true;
    }
	
    public function getpid()
    {
        global $internal_pid;
        if ($internal_pid != 0) {
            return $internal_pid;
        }
        $ip = 0;
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = ip2long($_SERVER['REMOTE_ADDR']);
        }
        $port = 0;
        if (isset($_SERVER['REMOTE_PORT'])) {
            $port = (int)$_SERVER['REMOTE_PORT'];
        }
        mt_srand(time() + (int)microtime() + $ip + $port + @getmypid());
        $internal_pid = mt_rand();
        return $internal_pid;
    }
	
	public function getTableHistory($server,$backto = 8){
		
		$dbHistory = '`' . $server['dbName'] . '`.`bi_history`';
		return $dbHistory;
			
		$monthNow = date('n');
		$dayNow = date('j');
		$yearNow = date('Y');
		$dateNow = date('Y-m-j');

		$newDate = strtotime ('-'.$backto.' day' , strtotime ( $dateNow ) ) ;
		
		$monthPrevious = date ( 'n' , $newDate );
		$yearPrevious = date ( 'Y' , $newDate );
	
		$dbHistory = '`' . $this->dbNameCore . '`.`bi_history_'.$server['idServer'].'_'.$yearPrevious.'_'.$monthPrevious.'`';
		
		$valid = $this->queryFetch("SELECT count(*) FROM $dbHistory LIMIT 1");
		
		if($valid !== false) {
			return $dbHistory;
		} else {
			$dbHistory = '`' . $server['dbName'] . '`.`bm_history`';
			return $dbHistory;
		}
	}

    public function setParam($param, $value = '')
    {
		$valid = $this->query("UPDATE `Parametros` SET  `valor` = '$value' WHERE  `nombre`= '$param';");
				
		if($valid){
			return true;
		} else {
			return false;
		}		
    }
	
    public function getParam($param, $default = "FALSE_NOK", $validEmpty = true)
    {

        $paramDbSql = "/* Monitor */ SELECT `valor`,`type` FROM `Parametros` WHERE `nombre` =  '$param' AND `visible` = 'true' LIMIT 1";
        
        $paramDb = $this->queryFetch($paramDbSql);

        //Inicio de validaciones
        if (($paramDb) && (count($paramDb) == 1)) {
            $type = $paramDb[0]['type'];
            if ($type == 'string') {
                $value = (string)$paramDb[0]['valor'];
            } elseif ($type == 'int') {
                $value = (int)$paramDb[0]['valor'];
            } elseif ($type == 'bool') {
                $value = (bool)$paramDb[0]['valor'];
            }

            if ($validEmpty == false) {
                return $value;
            } else {
                if ($value !== '') {
                    return $value;
                }
            }
        }
      
       	return $default;
    }
}
?>