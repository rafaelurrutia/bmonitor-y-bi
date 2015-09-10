<?php
if (!class_exists("Parametrosbd")) {
    Class Parametrosbd
    {

        private $parametro;
        private $conexion;
        private $logs;

        public function __construct($parametro, $conexion, $logs)
        {
            $this -> parametro = $parametro;
            $this -> conexion = $conexion;
            $this -> logs = $logs;
        }

        public function get($param, $default = "FALSE_NOK")
        {

            $param_bd_sql = "/* Monitor */ SELECT `valor`,`type` FROM `Parametros` WHERE `nombre` =  '$param' AND `visible` = 'true' LIMIT 1";
            $param_bd = $this -> conexion -> queryFetch($param_bd_sql, 'logs_param');

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

                $this -> logs -> debug("Parametro $param existe en la BD por lo cual se privilegia y se utiliza valor:", $value, 'logs_param');
                return $value;
            } else {
                if (isset($this -> parametro[$param])) {
                    $this -> logs -> debug("Parametro $param no existe en la BD, por lo cual se ocupa valor del archivo de confirguracion", null, 'logs_param');
                    return $this -> parametro[$param];
                } elseif ($default !== "FALSE_NOK") {
                    return $default;
                } else {
                    $this -> logs -> error("Parametro $param no existe no existe en la BD, config y no contiene valor por defecto", null, 'logs_param');
                    return null;
                }
            }
        }

        public function set($key, $var)
        {
            return $this -> parametro -> set($key, $var);
        }

        public function remove($key)
        {
            return $this -> parametro -> remove($key);
        }

    }

}
?>