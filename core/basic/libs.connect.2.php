<?php
if (!class_exists("Connect")) {
    class Connect
    {
        static private $PDOInstance;

        private $parametro;
        private $statement;
        private $methodconnect = 'tcp';
        private $countstatement = 0;
        private $prepare;

        public function __construct($parametro, $logs)
        {
            $this->parametro = $parametro;
            $this->logs = $logs;

            $this->parametro->set('LOGS_FILE', 'logs_db');

            $this->connecting();
        }

        public function timeStart()
        {
            $this->timeStart = microtime(true);
        }

        public function timeEnd()
        {
            $time_end = microtime(true);
            $time = $time_end - $this->timeStart;
            $time = round($time, 10);
            unset($this->timeStart);
            return $time;
        }

        public function connecting()
        {

            if (isset($this->parametro['MethodConn'])) {
                switch ($this->parametro['MethodConn']) {
                case 'unix_socket' :
                    $this->methodconnect = 'unix_socket';
                    break;
                case 'tcp' :
                    $this->methodconnect = 'tcp';
                    break;
                default :
                    $this->methodconnect = 'tcp';
                    break;
                }
            } else {
                $this->methodconnect = 'tcp';
            }

            if ($this->methodconnect === 'unix_socket') {
                $this->dsn = $this->parametro['MotorBD'] . ":unix_socket=" . $this->parametro['HostBD'];
                $this->dsn .= ";dbname=" . $this->parametro['NameBD'];
            } else {
                $this->dsn = $this->parametro['MotorBD'] . ":host=" . $this->parametro['HostBD'];
                $this->dsn .= ";dbname=" . $this->parametro['NameBD'];
            }

            if (!self::$PDOInstance) {
                try {

                    self::$PDOInstance = new PDO($this->dsn, $this->parametro['UserBD'], $this->parametro['PassBD']);
                    self::$PDOInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$PDOInstance->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8");
                    self::$PDOInstance->setAttribute(PDO::ATTR_PERSISTENT, false);

                } catch (PDOException $e) {
                    $this->logs->error("Error al conectar: ", $e->getMessage());
                    die("Error al conectar: " . $e->getMessage() . "<br/>");
                }
            }
        }

        function quote($str, $parameter_type = PDO::PARAM_STR)
        {
            if (is_null($str)) {
                return "NULL";
            }
            return self::$PDOInstance->quote($str, $parameter_type);
        }

        public function InicioTransaccion()
        {
            return self::$PDOInstance->beginTransaction();
        }

        public function commit()
        {
            return self::$PDOInstance->commit();
        }

        public function rollBack()
        {
            return self::$PDOInstance->rollBack();
        }

        public function errorInfo()
        {
            return self::$PDOInstance->errorInfo();
        }

        public function numRow($statement)
        {
            return self::$PDOInstance->exec($statement);
        }

        public function rowCount()
        {
            $regex = '/^SELECT\s+(?:ALL\s+|DISTINCT\s+)?(?:.*?)\s+FROM\s+(.*)$/i';
            if (preg_match($regex, $this->queryString, $output) > 0) {
                $stmt = self::$PDOInstance->query("SELECT COUNT(*) FROM {$output[1]}", PDO::FETCH_NUM);

                return $stmt->fetchColumn();
            }

            return FALSE;
        }

        public function lastInsertId()
        {
            return self::$PDOInstance->lastInsertId();
        }

        public function exec($query)
        {
            return self::$PDOInstance->exec($query);
        }

        public function prepare($query)
        {
            $this->statement = $query;
            $this->countstatement++;

            try {
                $this->prepare = self::$PDOInstance->prepare($query);
                return $this->prepare;
            } catch(PDOException $ex) {
                $this->logs->error("QUERY_PREPARE_FAIL QUERY[ $this->countstatement ] : ", $query);
                $this->logs->error("QUERY_PREPARE_FAIL EX[ $this->countstatement ] : ", $ex->getMessage());
                $this->prepare = false;
                return false;
            }

        }

        public function execute($query_prepare, $fetchTYPE = 'ASSOC', $logs = 'logs_db', $all = false)
        {
            $this->timeStart();

            if ($this->prepare === false) {
                $this->logs->error("QUERY_EXECUTE_FAIL QUERY[ $this->countstatement ] : ", "Prepare failed or not found");
                return false;
            }

            try {
                $queryResult = $this->prepare->execute();
                $count = $this->prepare->rowCount();
                $time = $this->timeEnd();
                $this->logs->debug("Query [ $this->countstatement ] execute ok , time: $time");
            } catch(PDOException $ex) {
                $this->logs->error("QUERY_EXECUTE_FAIL QUERY[ $this->countstatement ] : ", $query);
                $this->logs->error("QUERY_EXECUTE_FAIL EX[ $this->countstatement ] : ", $ex->getMessage());
                return false;
            }

            if ($count == 1 && $all == false) {
                $fetch = "fetch";
            } elseif ($count >= 1) {
                $fetch = "fetchAll";
            } else {
                return false;
            }

            if ($fetchTYPE == 'ASSOC') {
                $data = $queryResult->$fetch(PDO::FETCH_ASSOC);
            } elseif ($fetchTYPE == 'NUM') {
                $data = $queryResult->$fetch(PDO::FETCH_NUM);
            } elseif ($fetchTYPE == 'OBJ') {
                $data = (object)$queryResult->$fetch(PDO::FETCH_OBJ);
            } elseif ($fetchTYPE == 'BOTH') {
                $data = $queryResult->$fetch(PDO::FETCH_BOTH);
            } else {
                $data = $queryResult->$fetch(PDO::FETCH_ASSOC);
            }

            if (is_object($data)) {
                $data->rowCount = $count;
            } else {
                $data['rowCount'] = $count;
            }

            $queryResult->closeCursor();

            return $data;
        }

        public function query($statement, $omitirDuplicado = false)
        {
            $this->timeStart();
            $this->countstatement++;
            $this->statement = $statement;
            try {
                $query = self::$PDOInstance->query($statement);
                $time = $this->timeEnd();
                $this->logs->debug("[QUERY] Query  [ $this->countstatement ] execute ok , time: $time");
                if ($omitirDuplicado === true) {
                    $result['status'] = true;
                    return (object)$result;
                } else {
                    return true;
                }
            } catch(PDOException $ex) {
                $this->logs->error("QUERY_EXECUTE_FAIL QUERY[ $this->countstatement ] : ", $query);
                $this->logs->error("QUERY_EXECUTE_FAIL EX[ $this->countstatement ] : ", $ex->getMessage());
                $this->logs->error("QUERY_EXECUTE_FAIL EXCODE[ $this->countstatement ] : ", $ex->getCode());
                if ($omitirDuplicado === true) {
                    $result['duplicate'] = false;
                    $result['status'] = false;
                    return (object)$result;
                } else {
                    return false;
                }
            }

            if ((int)$error[1] == 1062) {
                $this->logs->debug("QUERY_ESTADO: Duplicate entry TIME: $time QUERY: ", $statement, $type);
                $this->logs->debug("Se omite Duplicate entry", '', 'logs_db');
                $result['duplicate'] = true;
                $result['status'] = FALSE;
                return (object)$result;
            } else {
                $this->logs->error("QUERY_ESTADO: Error TIME: $time QUERY: ", $statement, $type);
                $this->logs->error("QUERY_ERROR: ", $error[2], 'logs_db');
                $result['duplicate'] = FALSE;
                $result['status'] = FALSE;
                return (object)$result;
            }
        }

        public function queryFetch()
        {
            $this->timeStart();

            //Valores

            $args = func_get_args();

            $statement = array_shift($args);

            $statement = str_replace("?", "%s", $statement);

            $this->countstatement++;

            foreach ($args as $key => $value) {

                if (preg_match("/^logs_/i", $value)) {
                    $type = $value;
                } else {
                    $args_value[] = $value;
                }
            }

            if (isset($args_value) && count($args_value) > 0) {
                array_unshift($args_value, $statement);

                $statement = @call_user_func_array('sprintf', $args_value);

                if (!$statement) {
                    return FALSE;
                }

            }

            if (empty($type)) {
                $type = 'logs_db';
            }

            $this->statement = $statement;

            try {
                $queryResult = self::$PDOInstance->query($statement);
                $time = $this->timeEnd();
                $fetch = $queryResult->fetchAll(PDO::FETCH_ASSOC);
                $this->logs->debug("QUERY_ESTADO: OK TIME: $time QUERY: ", $statement);
                return $fetch;
            } catch(PDOException $ex) {
                $this->logs->error("QUERY_EXECUTE_FAIL QUERY[ $this->countstatement ] : ", $statement);
                $this->logs->error("QUERY_EXECUTE_FAIL EX[ $this->countstatement ] : ", $ex->getMessage());
                $this->logs->error("QUERY_EXECUTE_FAIL EXCODE[ $this->countstatement ] : ", $ex->getCode());
                return false;
            }
        }

        public function queryArray($statement, $numeric = FALSE, $type = 'logs_db')
        {
            $time_start = microtime(true);
            $query = self::$PDOInstance->query($statement);
            $time_end = microtime(true);

            $time = $time_end - $time_start;

            if (!$query) {
                $error = $this->errorInfo();
                $this->logs->error("QUERY_ESTADO: Error TIME: $time QUERY: ", $statement, $type);
                $this->logs->error("QUERY_ERROR: ", $error, $type);
                return FALSE;
            } else {
                $this->logs->debug("QUERY_ESTADO: OK TIME: $time QUERY: ", $statement, $type);
                $i = 0;
                foreach ($query as $query2) {
                    if ($numeric) {
                        $queryReturn[$i] = $query2[0];
                    } else
                        $queryReturn[$i] = $query2;
                    $i++;
                }
                if ($i > 1) {
                    return $queryReturn;
                } else {
                    if (!isset($queryReturn)) {
                        if (empty($queryReturn)) {
                            return FALSE;
                        } else
                            return $queryReturn[0];
                    } else
                        return $queryReturn[0];
                }
            }
        }

        public function queryFetchAllAssoc($statement, $type = 'logs_db')
        {
            $time_start = microtime(true);
            $this->queryString = $statement;
            $result = self::$PDOInstance->query($statement);
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            if ($result === FALSE) {
                $error = $this->errorInfo();
                $this->logs->error("QUERY_ESTADO: Error TIME: $time QUERY: ", $statement, $type);
                $this->logs->error("QUERY_ERROR: ", $error, $type);
            } else {
                $this->logs->debug("QUERY_ESTADO: OK TIME: $time QUERY: ", $statement, $type);
                return $result->fetchAll(PDO::FETCH_ASSOC);
            }

        }

        public function queryFetchRowAssoc($statement, $type = 'logs_db')
        {
            $time_start = microtime(true);
            $this->queryString = $statement;
            $result = self::$PDOInstance->query($statement);
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            if ($result === FALSE) {
                $error = $this->errorInfo();
                $this->logs->error("QUERY_ESTADO: Error TIME: $time QUERY: ", $statement, $type);
                $this->logs->error("QUERY_ERROR: ", $error, $type);
            } else {
                $this->logs->debug("QUERY_ESTADO: OK TIME: $time QUERY: ", $statement, $type);
                return $result->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        public function queryFetchColAssoc($statement, $type = 'logs_db')
        {
            $time_start = microtime(true);
            $this->queryString = $statement;
            $result = self::$PDOInstance->query($statement);
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            if ($result === FALSE) {
                $error = $this->errorInfo();
                $this->logs->error("QUERY_ESTADO: Error TIME: $time QUERY: ", $statement, $type);
                $this->logs->error("QUERY_ERROR: ", $error, $type);
            } else {
                $this->logs->debug("QUERY_ESTADO: OK TIME: $time QUERY: ", $statement, $type);
                return $result->fetchColumn();
            }
        }

        public function arrayToIN($array, $valueName = FALSE, $numeric = FALSE)
        {

            if (is_array($array)) {

                $datosArray = array();
                foreach ($array as $key => $value) {
                    if ($valueName === FALSE) {
                        if ($numeric) {
                            if (is_numeric($value)) {
                                $datosArray[] = $value;
                            }
                        } else {
                            $datosArray[] = $value;
                        }
                    } else {
                        if ($numeric) {
                            if (is_numeric($value)) {
                                $datosArray[] = $value[$valueName];
                            }
                        } else {
                            $datosArray[] = $value[$valueName];
                        }

                    }
                }

                $resultIN = '(' . join(',', $datosArray) . ')';

                return $resultIN;
            } else {
                return FALSE;
            }
        }

        public function arrayIN($array, $valueName = false, $alwayNumeric = false)
        {

            if (is_array($array)) {

                $datosArray = array();

                foreach ($array as $key => $value) {
                    if ($valueName === false) {
                        if ($alwayNumeric) {
                            if (is_numeric($value)) {
                                $datosArray[] = self::$PDOInstance->quote($value);
                            }
                        } else {
                            $datosArray[] = self::$PDOInstance->quote($value);
                        }
                    } else {
                        if ($alwayNumeric) {
                            if (is_numeric($value[$valueName])) {
                                $datosArray[] = self::$PDOInstance->quote($value[$valueName]);
                            }
                        } else {
                            $datosArray[] = self::$PDOInstance->quote($value[$valueName]);
                        }
                    }
                }

                $resultIN = '(' . join(',', $datosArray) . ')';

                return $resultIN;
            } else {
                return false;
            }
        }

    }

}
?>