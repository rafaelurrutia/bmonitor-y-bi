<?php
Class Parametrosdb
{
    private $parametro;
    private $conexion;

    private $caching = false;
    private $timeCaching = 3000;
    private $company = false;
    
    private $param;
    private $paramCompany;

    public function __construct($parametro, $conexion)
    {
        $this->parametro = $parametro;
        $this->conexion = $conexion;
    }

    private function getCache($param)
    {
        $cacheDir = site_path . 'cache/';
        
        if($param != ''){
            
            $cacheFileParam = $cacheDir . md5($param) . ".cache"; 
            
            if(file_exists($cacheFileParam) && (fileatime($cacheFileParam) + $this->timeCaching) > time()) {
                return file_get_contents($cacheFileParam);
            } else {
                return false;
            }
            
        } else {
            return false;
        }
    }

    private function setCache($param, $value)
    {
    	if($this->caching == false){
    		return false;
    	}
		
        $cacheDir = site_path . 'cache/';
        
        if($param != '' && $value != ''){
            
            $cacheFileParam = $cacheDir . md5($param) . ".cache"; 
            
            $fp = fopen($cacheFileParam, 'w');
            $fwrite = fwrite($fp, $value);
            fclose($fp);
            if ($fwrite === false) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    public function get($param, $default = "FALSE_NOK", $empty = TRUE)
    {
        if ($this->caching === true) {
            $paramResult = $this->getCache($param);
            if($paramResult !== false) {
                return $paramResult;
            }
        }

        $paramDbSql = "/* Monitor */ SELECT `valor`,`type` FROM `Parametros` WHERE `nombre` =  '$param' AND `visible` = 'true' LIMIT 1";
        
        $paramDb = $this->conexion->queryFetch($paramDbSql, 'logs_param');

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
            
            $this->setCache($param,$value);

            if (!$empty) {
                return $value;
            } else {
                if ($value !== '') {
                    return $value;
                }
            }
        }
        
        if (isset($this->parametro[$param])) {
            return $this->parametro[$param];
        } elseif ($default !== "FALSE_NOK") {
            return $default;
        } else {
            return null;
        }
    }

    public function config($param, $default = "FALSE_NOK", $company = false)
    {

        if (isset($_SESSION['idcompany'])) {
            $company = $_SESSION['idcompany'];
        } elseif ($company !== false && is_numeric($company)) {
            $company = (int)$company;
        } else {
            return false;
        }

        if ($this->caching === true) {
            $paramResult = $this->getCache($company.$param);
            if($paramResult !== false) {
                return $paramResult;
            }        
        }

        $param_bd_sql = "/* Monitor */ SELECT `valor`,`type` FROM `Empresas_Param` WHERE  `id_empresa`  = " . $company . " AND `nombre` =  '$param' AND `visible` = 'true' LIMIT 1";
        $param_bd = $this->conexion->queryFetch($param_bd_sql, 'logs_param');

        //Inicio de validaciones
        if (($param_bd) && (count($param_bd) == 1)) {
            $type = $param_bd[0]['type'];
            if ($type == 'string') {
                $value = (string)$param_bd[0]['valor'];
            } elseif ($type == 'int') {
                $value = (int)$param_bd[0]['valor'];
            } elseif ($type == 'bool') {
                $value = (bool)$param_bd[0]['valor'];
            }
            $this->setCache($company.$param,$value);
            return $value;
        } else {
            if ($default !== "FALSE_NOK") {
                return $default;
            } else {
                return null;
            }
        }
    }

    public function setdb($param, $value = '')
    {
		$valid = $this->conexion->query("UPDATE `Parametros` SET  `valor` = '$value' WHERE  `nombre`= '$param';");
				
		if($valid){
			return true;
		} else {
			return false;
		}		
    }

    public function set($key, $var)
    {
        return $this->parametro->set($key, $var);
    }

    public function remove($key)
    {
        return $this->parametro->remove($key);
    }

}
?>