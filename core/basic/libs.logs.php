<?php
class Logs
{
    private $parametro;

    function __construct($parametro)
    {
        $this->parametro = $parametro;

        $this->intererror = array(
            "NOTWRITABLE" => "El archivo de logs seleccionado no se puede escribir",
            "NOFILESETTING" => "Configuracion incorrecta!",
            "FILEWRITINGERROR" => "HO, ocurrio un error al escribir"
        );
    }

    private function file($archivo)
    {

        $archivo_def = "/tmp/logKairoSinDireccionar";

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

    private function logger($nivel, $texto, $extra = null, $file)
    {

        $archivo_def = "/tmp/logKairoSinDireccionar";

        if (isset($this->parametro['LOGS_APPS'])) {
            $archivo = LOGS . $this->parametro['LOGS_APPS'];
        } else {
            $archivo = $archivo_def;
        }

        if (($file == 'logs') && ($this->parametro['LOGS_FILE'])) {
            $file = $this->parametro['LOGS_FILE'];
        }

        if ($file == 'logs') {
            $archivo = $archivo . ".log." . date("Y-m-d");
        } else {
            $file_part = explode("_", $file);
            $file_prefic = $file_part[1];

            if (!empty($file_prefic)) {
                if ($file_prefic == 'db') {
                    $db_debug = $this->parametro->get('LOG_DB', true);
                    if ($db_debug) {
                        $archivo = $archivo . "_db.log." . date("Y-m-d");
                    } else {
                        return false;
                    }
                } elseif ($file_prefic == 'param') {
                    $param_debug = $this->parametro->get('LOG_PARAM', false);
                    if ($param_debug) {
                        $archivo = $archivo . "_param.log." . date("Y-m-d");
                    } else {
                        return false;
                    }
                } else {
                    $archivo = $archivo . "_" . $file_prefic . ".log." . date("Y-m-d");
                }
            } else {
                $archivo = $archivo . ".log." . date("Y-m-d");
            }
        }

        $FILE = $this->file($archivo);
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
        if ((2 > 2) || ($this->parametro['STDOUT'] == true)) {
            echo $linea . "\n";
        }

        $fp = fopen($archivo, "a");
        if ($fp != null) {
            fwrite($fp, $linea);
            fclose($fp);
        }
    }

    public function info($msg, $data = false, $file = 'logs')
    {
        $this->logger("INFO", $msg, $data, $file);
    }

    public function debug($msg, $data = false, $file = 'logs')
    {
        $d_debug = $this->parametro->get('DEBUG', true);
        if ($d_debug) {
            $this->logger("DEBUG", $msg, $data, $file);
        }
    }

    public function warning($msg, $data = false, $file = 'logs')
    {
        $this->logger("WARNING", $msg, $data, $file);
    }

    public function error($msg, $data = false, $file = 'logs')
    {
        $this->logger("ERROR", $msg, $data, $file);
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

}
?>