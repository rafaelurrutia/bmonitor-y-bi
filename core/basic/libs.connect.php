<?php
class pdoDbException extends PDOException
{

    public function __construct(PDOException $e)
    {
        if (strstr($e->getMessage(), 'SQLSTATE[')) {
            preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $e->getMessage(), $matches);
            $this->code = ($matches[1] == 'HT000' ? $matches[2] : $matches[1]);
            $this->message = $matches[3];
        }
    }

}

if (!class_exists("Connect")) {
    class Connect
    {
        static private $PDOInstance;
        private $parametro;

        public function __construct($parametro, $logs)
        {
            $this->parametro = $parametro;
            $this->logs = $logs;

            $this->connecting();
        }

        public function connecting()
        {

            $now = new DateTime();
            $mins = $now->getOffset() / 60;

            $sgn = ($mins < 0 ? -1 : 1);
            $mins = abs($mins);
            $hrs = floor($mins / 60);
            $mins -= $hrs * 60;

            $offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);

            // @formatter:off
            $this -> dsn = $this -> parametro['MotorBD'] . ":host=" . $this -> parametro['HostBD'] . ";dbname=" 
                    . $this -> parametro['NameBD'];
            // @formatter:on
            if (!self::$PDOInstance) {
                try {

                    self::$PDOInstance = new PDO($this->dsn, $this->parametro['UserBD'], $this->parametro['PassBD']);

                    // self::$PDOInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$PDOInstance->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8");
                    self::$PDOInstance->setAttribute(PDO::ATTR_PERSISTENT, false);
                    self::$PDOInstance->exec("SET time_zone = '$offset';");

                } catch (PDOException $e) {
                    header('HTTP/1.0 500 Internal Server Error');
                   	// $this->logs->error("Error al conectar: ", $e->getMessage());
					echo "Error al conectar: " . $e->getMessage() . "<br/>";
					exit;
                }
            }
        }

        public function connectR($MotorBD, $HostBD, $UserBD, $PassBD, $NameBD)
        {
            $dsn = $MotorBD . ":host=" . $HostBD . ";dbname=" . $NameBD;

            try {
                // @formatter:off
                $PDOInstance = new PDO($dsn, $UserBD, $PassBD, array(
                    PDO::ATTR_PERSISTENT => false, 
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                // @formatter:on
            } catch (PDOException $e) {
                $this->logs->error("Error al conectar: ", $e->getMessage());
                return false;
            }

            return $PDOInstance;
        }
        
        public function changeDB($dbname, $logs = 'logs_db')
        {
            $result = self::$PDOInstance->exec("USE `$dbname`");
            
            if ($result) {
                return true;
            } else {
                $error = $this->errorInfo();
                $SQLSTATE_CODE = $error[0];
                $DRIVER_ERORR_CODE = $error[1];
                
                if($DRIVER_ERORR_CODE == '') {
                    return true;
                } else {            
                    $DRIVER_ERORR_MESSAGE = $error[2];
                    $this->logs->error("QUERY_FAIL QUERY:", "USE $dbname", $logs);
                    $this->logs->error("QUERY_FAIL CODE: $DRIVER_ERORR_CODE MSG: ", $DRIVER_ERORR_MESSAGE, $logs);
                    return false;
                }
            }

        }

        public function quote($str, $parameter_type = PDO::PARAM_STR)
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

            return false;
        }

        public function lastInsertId()
        {
            return self::$PDOInstance->lastInsertId();
        }

        public function exec($query)
        {
            return self::$PDOInstance->exec($query);
        }

        public function prepare($query, $logs = 'logs_db')
        {
            $prepare = self::$PDOInstance->prepare($query);
            if ($prepare) {
                return $prepare;
            } else {
                $error = $prepare->errorInfo();
                $SQLSTATE_CODE = $error[0];
                $DRIVER_ERORR_CODE = $error[1];
                $DRIVER_ERORR_MESSAGE = $error[2];
                $this->logs->error("QUERY_FAIL QUERY:", $query, $logs);
                $this->logs->error("QUERY_FAIL CODE: $DRIVER_ERORR_CODE MSG: ", $DRIVER_ERORR_MESSAGE, $logs);
                return $prepare;
            }
        }

        public function execute($query_prepare, $fetchTYPE = 'ASSOC', $logs = 'logs_db', $all = false)
        {
            try {
                $query_result = $query_prepare->execute();

                $count = $query_prepare->rowCount();

                if ($count == 1 && $all == false) {
                    $fetch = "fetch";
                } elseif ($count >= 1) {
                    $fetch = "fetchAll";
                } else {
                    return false;
                }

                if ($fetchTYPE == 'ASSOC') {
                    $data = $query_prepare->$fetch(PDO::FETCH_ASSOC);
                } elseif ($fetchTYPE == 'NUM') {
                    $data = $query_prepare->$fetch(PDO::FETCH_NUM);
                } elseif ($fetchTYPE == 'OBJ') {
                    $data = (object)$query_prepare->$fetch(PDO::FETCH_OBJ);
                } elseif ($fetchTYPE == 'BOTH') {
                    $data = $query_prepare->$fetch(PDO::FETCH_BOTH);
                } else {
                    $data = $query_prepare->$fetch(PDO::FETCH_ASSOC);
                }

                if (is_object($data)) {
                    $data->rowCount = $count;
                } else {
                    $data['rowCount'] = $count;
                }
                return $data;
            } catch (PDOException $e) {

                $this->logs->error("QUERY_FAIL: ", $e->getMessage(), $logs);

                return false;
            }
        }

        public function execute1($query_prepare, $fetch = false, $fetchTYPE = 'ASSOC', $logs = 'logs_db', $all = false)
        {
            $query_result = $query_prepare->execute();

            if ($query_result) {

                if ($fetch) {

                    $count = $query_prepare->rowCount();

                    if ($count == 1 && $all == false) {
                        $fetch = "fetch";
                    } elseif ($count >= 1) {
                        $fetch = "fetchAll";
                    } else {
                        return false;
                    }

                    if ($fetchTYPE == 'ASSOC') {
                        $data = $query_prepare->$fetch(PDO::FETCH_ASSOC);
                    } elseif ($fetchTYPE == 'NUM') {
                        $data = $query_prepare->$fetch(PDO::FETCH_NUM);
                    } elseif ($fetchTYPE == 'OBJ') {
                        $data = (object)$query_prepare->$fetch(PDO::FETCH_OBJ);
                    } elseif ($fetchTYPE == 'BOTH') {
                        $data = $query_prepare->$fetch(PDO::FETCH_BOTH);
                    } else {
                        $data = $query_prepare->$fetch(PDO::FETCH_ASSOC);
                    }

                    if (is_object($data)) {
                        $data->rowCount = $count;
                    } else {
                        $data['rowCount'] = $count;
                    }

                } else {
                    $data = $this->lastInsertId();
                }
                $query_prepare->closeCursor();
                return $data;
            } else {
                $error = $query_prepare->errorInfo();
                $SQLSTATE_CODE = $error[0];
                $DRIVER_ERORR_CODE = $error[1];
                $DRIVER_ERORR_MESSAGE = $error[2];
                // @formatter:off
                $this -> logs -> error("QUERY_FAIL STATE: $SQLSTATE_CODE CODE: $DRIVER_ERORR_CODE MSG: ", 
                            $DRIVER_ERORR_MESSAGE, $logs);
                // @formatter:on
                return false;
            }
        }

        public function execute_query($query_prepare, $logs = 'logs_db')
        {
            try {
                $query_result = $query_prepare->execute();
            } catch (Exception $e) {
                return false;
            }

            if ($query_result) {
                $query_prepare->closeCursor();
                return $query_result;
            } else {
                $error = $query_prepare->errorInfo();
                $SQLSTATE_CODE = $error[0];
                $DRIVER_ERORR_CODE = $error[1];
                $DRIVER_ERORR_MESSAGE = $error[2];
                // @formatter:off
                $this -> logs -> error("QUERY_FAIL STATE: $SQLSTATE_CODE CODE: $DRIVER_ERORR_CODE MSG: ", 
                    $DRIVER_ERORR_MESSAGE, $logs);
                // @formatter:on
                return $query_result;
            }
        }

        public function query_adv($query, $param = false, $result = 'ASSOC', $logs = 'logs_db')
        {
            $prepare = self::$PDOInstance->prepare($query);

            if ($prepare) {

                if ($param) {

                    foreach ($param as $key => $value) {
                        if ($value['type'] == 'STR') {
                            $prepare->bindParam($key, $value['value'], PDO::PARAM_STR);
                        } elseif ($value_sql_type == 'INT') {
                            $prepare->bindParam($key, $value['value'], PDO::PARAM_INT);
                        }
                    }

                    $query_result = $prepare->execute();

                    if ($query_result) {
                        $data = $prepare->fetch(PDO::FETCH_ASSOC);
                        $data['rowCount'] = $prepare->rowCount();
                        $prepare->closeCursor();
                        return $data;
                    } else {
                        $error = $prepare->errorInfo();
                        $SQLSTATE_CODE = $error[0];
                        $DRIVER_ERORR_CODE = $error[1];
                        $DRIVER_ERORR_MESSAGE = $error[2];
                        // @formatter:off
                        $this -> logs -> error("QUERY_FAIL CODE: $DRIVER_ERORR_CODE MSG: ", 
                                        $DRIVER_ERORR_MESSAGE, $logs);
                        // @formatter:on
                        return false;
                    }
                } else {
                    $query_result = $prepare->execute();

                    if ($query_result) {

                        $data['rowCount'] = $prepare->rowCount();
                        $data['data'] = $prepare->fetch($result);

                        $prepare->closeCursor();

                        return $data;
                    } else {

                        $error = $prepare->errorInfo();
                        $SQLSTATE_CODE = $error[0];
                        $DRIVER_ERORR_CODE = $error[1];
                        $DRIVER_ERORR_MESSAGE = $error[2];
                        // @formatter:off
                        $this -> logs -> error("QUERY_FAIL CODE: $DRIVER_ERORR_CODE MSG: ", 
                            $DRIVER_ERORR_MESSAGE, $logs);
                        // @formatter:on
                        return false;

                    }
                }

            } else {
                $error = $prepare->errorInfo();
                $SQLSTATE_CODE = $error[0];
                $DRIVER_ERORR_CODE = $error[1];
                $DRIVER_ERORR_MESSAGE = $error[2];

                $this->logs->error("QUERY_FAIL CODE: $DRIVER_ERORR_CODE MSG: ", $DRIVER_ERORR_MESSAGE, $logs);
                return false;
            }

        }

		public function queryRetry($statement,$retry = 1,$type = 'logs_db')
		{
            $this->queryString = $statement;
            $query = self::$PDOInstance->query($statement);

			if (!$query) {
				for ($i=0; $i < $retry; $i++) { 
					$error = $this->errorInfo();		
					if (strpos($error[2],'Lock wait timeout exceeded') !== false) {
						sleep(2);
	    				$query = self::$PDOInstance->query($statement);
	    				if($query){
							return true;
	    				}
					}
				}
                $this->logs->error($statement ." :::::: ", $error[2], $type);					
				return false;	
			} else {
				return true;
			}
		}

        public function query($statement, $omitirDuplicado = false, $type = 'logs_db')
        {
            $time_start = microtime(true);

            $this->queryString = $statement;

            $query = self::$PDOInstance->query($statement);

            $time_end = microtime(true);

            $time = $time_end - $time_start;
            $time = round($time, 10);

            if (!$query) {
                $error = $this->errorInfo();

                if ($omitirDuplicado) {
                    if ((int)$error[1] == 1062) {
                        $this->logs->debug("QUERY_ESTADO: Duplicate entry TIME: $time QUERY: ", $statement, $type);
                        $this->logs->debug("Se omite Duplicate entry", '', 'logs_db');
                        $result['duplicate'] = true;
                        $result['status'] = false;
                        return (object)$result;
                    } else {
                        $this->logs->error("QUERY_ESTADO: Error TIME: $time QUERY: ", $statement, $type);
                        $this->logs->error("QUERY_ERROR: ", $error[2], 'logs_db');
                        $result['duplicate'] = false;
                        $result['status'] = false;
                        return (object)$result;
                    }
                } else {
                    $this->logs->error("QUERY_ESTADO: Error TIME: $time QUERY: ", $statement, $type);
                    $this->logs->error("QUERY_ERROR: ", $error[2], $type);
                    return false;
                }
            } else {
                $this->logs->debug("QUERY_ESTADO: OK TIME: $time QUERY: ", $statement, $type);
                if ($omitirDuplicado) {
                    $result['status'] = true;
                    return (object)$result;
                } else {
                    return true;
                }
            }
        }

        public function insert($statement, $nextRowset = false, $type = 'logs_db')
        {
            $time_start = microtime(true);

            $this->queryString = $statement;

            $query = self::$PDOInstance->query($statement);

            $time_end = microtime(true);

            $time = $time_end - $time_start;
            $time = round($time, 10);

            if (!$query) {
                $error = $this->errorInfo();
                $this->logs->error("QUERY_ESTADO: Error TIME: $time QUERY: ", $statement, $type);
                $this->logs->error("QUERY_ERROR: ", $error[2], $type);
                return false;
            } else {
                $this->logs->debug("QUERY_ESTADO: OK TIME: $time QUERY: ", $statement, $type);
                if ($nextRowset) {
                    $query->nextRowset();
                }

                $rowTd = $query->fetch(PDO::FETCH_NUM);

                return $this->lastInsertId();
            }
        }

        public function queryFetch2($statement, $type = 'logs_db')
        {
            $time_start = microtime(true);
            $args = func_get_args();
            $statement = array_shift($args);
            $statement = str_replace("?", "%s", $statement);
            //$args  = array_map('mysql_real_escape_string', $args);
            array_unshift($args, $statement);
            $statement = call_user_func_array('sprintf', $args);
            $this->queryString = $statement;
            $query = self::$PDOInstance->query($statement);

            $time_end = microtime(true);

            $time = $time_end - $time_start;
            $time = round($time, 10);

            if (!$query) {
                $error = $this->errorInfo();
                $this->logs->error("QUERY_ESTADO: Error TIME: $time QUERY: ", $statement, $type);
                $this->logs->error("QUERY_ERROR: ", $error, $type);
                return false;
            } else {
                $this->logs->debug("QUERY_ESTADO: OK TIME: $time QUERY: ", $statement, $type);
                return $query->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        public function queryFetch()
        {
            $time_start = microtime(true);

            //Valores

            $args = func_get_args();

            $statement = array_shift($args);

            $statement = str_replace("?", "%s", $statement);

            foreach ($args as $key => $value) {

                if (preg_match("/^logs_/i", $value)) {
                    $type = $value;
                } else {
                    $args_value[] = $this->quote($value);
                }
            }

            if (isset($args_value) && count($args_value) > 0) {
                array_unshift($args_value, $statement);

                $statement = call_user_func_array('sprintf', $args_value);

                if (!$statement) {
                    return false;
                }

            }

            if (empty($type)) {
                $type = 'logs_db';
            }

            $this->queryString = $statement;

            unset($query);

            $query = self::$PDOInstance->prepare($statement);

            $result = $query->execute();

            $time_end = microtime(true);
            $time = $time_end - $time_start;
            $time = round($time, 10);

            if ($result) {
                $this->logs->debug("QUERY_ESTADO: OK TIME: $time QUERY: ", $statement, $type);
                try {
                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                } catch(PDOException $e) {
                    $res = true;
                }

                return $res;
            } else {

                $this->logs->error("QUERY_ESTADO: Error TIME: $time QUERY: ", $statement, $type);
                return false;
            }

        }

        public function queryExecuteFetch($statement, $param = array(), $type = 'logs_db')
        {
            $time_start = microtime(true);

            //Valores

            if (is_array($param) && count($param) > 0) {
                foreach ($param as $key => $value) {
                    $arg_arry[":" . $key] = $value;
                }
            }

            $this->queryString = $statement;

            unset($query);

            $query = self::$PDOInstance->prepare($statement);

            $result = $query->execute($arg_arry);

            $time_end = microtime(true);
            $time = $time_end - $time_start;
            $time = round($time, 10);

            if ($result) {
                $this->logs->debug("QUERY_ESTADO: OK TIME: $time QUERY: $statement", $param, $type);
                try {
                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
                } catch(PDOException $e) {
                    $res = true;
                }

                return $res;
            } else {

                $this->logs->error("QUERY_ESTADO: Error TIME: $time QUERY: ", $statement, $type);
                return false;
            }

        }

        public function queryArray($statement, $numeric = false, $type = 'logs_db')
        {
            $time_start = microtime(true);
            $query = self::$PDOInstance->query($statement);
            $time_end = microtime(true);

            $time = $time_end - $time_start;

            if (!$query) {
                $error = $this->errorInfo();
                $this->logs->error("QUERY_ESTADO: Error TIME: $time QUERY: ", $statement, $type);
                $this->logs->error("QUERY_ERROR: ", $error, $type);
                return false;
            } else {
                $this->logs->debug("QUERY_ESTADO: OK TIME: $time QUERY: ", $statement, $type);
                $i = 0;
                foreach ($query as $query2) {
                    if ($numeric) {
                        $queryReturn[$i] = $query2[0];
                    } else {
                        $queryReturn[$i] = $query2;
                    }
                    $i++;
                }
                if ($i > 1) {
                    return $queryReturn;
                } else {
                    if (!isset($queryReturn)) {
                        if (empty($queryReturn)) {
                            return false;
                        } else {
                            return $queryReturn[0];
                        }

                    } else {
                        return $queryReturn[0];
                    }
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
            if ($result === false) {
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
            if ($result === false) {
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
            if ($result === false) {
                $error = $this->errorInfo();
                $this->logs->error("QUERY_ESTADO: Error TIME: $time QUERY: ", $statement, $type);
                $this->logs->error("QUERY_ERROR: ", $error, $type);
            } else {
                $this->logs->debug("QUERY_ESTADO: OK TIME: $time QUERY: ", $statement, $type);
                return $result->fetchColumn();
            }
        }

        /*
         public function arrayToIN($array,$valueName = false){
         if(is_array($array)){
         $resultIN = '(';
         $count = count($array);
         $inicioArray = 0;
         foreach ($array as $key => $value) {
         if($valueName) {
         $resultIN .= $value[$valueName];
         } else {
         $resultIN .= $value;
         }
         $inicioArray++;

         if($count != $inicioArray) {
         $resultIN .= ',';
         }
         }
         $resultIN .= ')';
         return $resultIN;
         } else {
         return false;
         }
         }*/

        public function arrayToIN($array, $valueName = false, $numeric = false)
        {

            if (is_array($array)) {

                $datosArray = array();
                foreach ($array as $key => $value) {
                    if ($valueName === false) {
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
                return false;
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