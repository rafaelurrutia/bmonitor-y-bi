<?php
if (!class_exists("Graph")) {
    class Graph
    {
        var $table_cache = 'bm_cache_graph';

        public function __construct($parametro, $conexion, $logs)
        {
            $this->parametro = $parametro;
            $this->conexion = $conexion;
            $this->logs = $logs;
        }

        public function getGraph($idgraph, $name, $query, $force, $delay = 6000)
        {
            $this->logs->debug("Iniciando Libs Graph:", $idgraph, 'logs_graph');
            //Valida Cache
            $cacheValida = $this->cacheGraph($idgraph);

            if ($cacheValida) {

                return $cacheValida;

            } else {
                $cache_new = $this->createGraph($idgraph, $name, $query, $delay);
                if ($cache_new) {
                    return $cache_new;
                } else {
                    return false;
                }
            }
        }

        public function getTable($idgraph, $name, $query, $force, $delay = 6000)
        {
            //Valida Cache
            $cacheValida = $this->cacheGraph($idgraph);

            if ($cacheValida) {

                return $cacheValida;

            } else {
                $cache_new = $this->createGraph($idgraph, $name, $query, $delay, true);
                if ($cache_new) {
                    return $cache_new;
                } else {
                    return false;
                }
            }
        }

        private function cacheGraph($idgraph)
        {
            if (!empty($idgraph)) {

                $valida_cache = "SELECT `cache` FROM " . $this->table_cache . " WHERE `id_graph`='$idgraph' AND `fechahora` > DATE_ADD(NOW(), INTERVAL - 5 MINUTE)";

                $cache = $this->conexion->queryFetch($valida_cache, 'logs_cache');

                if ($cache) {
                    $this->logs->debug("Se utiliza cache", $idgraph, 'logs_cache');
                    return $cache[0]['cache'];
                } else {
                    $this->logs->error("No se encontro cache", $idgraph, 'logs_cache');
                    return false;
                }

            } else {
                return false;
            }
        }

        private function createGraph($idgraph, $name, $query, $delay, $array = false)
        {
            $this->logs->debug("Generado cache", $idgraph, 'logs_cache');
            $graph_dates = $this->conexion->queryFetch($query, 'logs_cache');

            if (!$graph_dates) {
                return false;
            } elseif (count($graph_dates) < 1) {
                return false;
            }

            if (!$array) {

                $data = array();
                $finished = false;
                $ultimo = end($graph_dates);
                $anterior = $graph_dates[0]['clock'];
                $delayMargen = $delay * 1300;
                $delay = $delay * 1000;

                foreach ($graph_dates as $key => $graph) {
                    while (!$finished) :

                        if (($anterior != $graph['clock']) && ($graph['clock'] != $ultimo["clock"]) && ($anterior + $delayMargen < $graph['clock'])) {
                            $anterior = $anterior + $delay;
                            $data[] = "[" . $anterior . ",null] \n";
                        } else {
                            $finished = true;
                        }
                    endwhile;
                    $finished = false;
                    $anterior = $graph['clock'];
                    $data[] = "[" . $graph['clock'] . "," . $graph['value'] . "]\n";
                }
                $data_final = '[' . join($data, ',') . ']';
            } else {
                $data_final = $graph_dates;
            }

            $insert_cache = "INSERT INTO `bm_cache_graph` (`id_graph`, `fechahora`, `cache`) VALUES ('$idgraph', NOW(), '$data_final') ON DUPLICATE KEY UPDATE `cache` = '$data_final';";

            $valida = $this->conexion->query($insert_cache, false, 'logs_cache');

            if ($valida) {
                return "$data_final";
            } else {
                return false;
            }
        }

        public function getGraph2($iditem, $host, $name, $force, $limitAsig = 0, $delay = 6000)
        {
            $GRAPH_CACHE_STATUS = $this->parametro->get('GRAPH_CACHE_STATUS', true);

            $idgraph = $iditem . "_" . $host;

            $this->logs->debug("[$idgraph] : Iniciando Libs Graph:", null, 'logs_cache');

            if (!isset($limitAsig) || $limitAsig == 0 || (!is_numeric($limitAsig))) {
                $LIMIT = $this->parametro->get('LIMIT_GRAPH', 1000);
            } else {
                $LIMIT = $limitAsig;
            }

            $SELECT = "SELECT `clock`*1000 as clock,`value`, `id_history` FROM `bm_history`";

            $WHERE = " WHERE `id_host` = $host  AND `id_item` = '$iditem'";

            $ORDER = " ORDER BY `clock` DESC LIMIT $LIMIT;";

            if ($GRAPH_CACHE_STATUS) {
                $valid_History_cache_sql = "SELECT `id_history` FROM `bm_cache_graph` WHERE `id_graph` = '$idgraph'";

                $valid_History_cache_result = $this->conexion->queryFetch($valid_History_cache_sql, 'logs_cache');

                $this->logs->debug("[$idgraph] : Validando parametro historico:", null, 'logs_cache');

                if ($valid_History_cache_result && (count($valid_History_cache_result) > 0)) {
                    $idHistory = $valid_History_cache_result[0]['id_history'];

                    if (is_numeric($idHistory)) {
                        $this->logs->debug("[$idgraph] : Iniciando desde el historico:", $idHistory, 'logs_cache');
                        $WHERE = $WHERE . " AND `id_history` > " . $idHistory;
                    }
                }
            }

            $query = $SELECT . $WHERE . $ORDER;

            $this->logs->debug("Solicitando Graph: ", $query, 'logs_graph');

            //Valida Cache

            if ($GRAPH_CACHE_STATUS) {
                $cacheValida = $this->cache($idgraph);
            } else {
                $cacheValida = false;
            }

            if ($cacheValida) {
                $this->logs->debug("[$idgraph] : Retornando cache del grafico:", null, 'logs_cache');

                return '[' . $cacheValida . ']';

            } else {

                $this->logs->debug("[$idgraph] : Generando cache , dado que no cumple o no se encuentra", null, 'logs_cache');

                $cache_new = $this->createGraph2($idgraph, $name, $query, $delay, $GRAPH_CACHE_STATUS);
                if ($cache_new) {
                    $this->logs->debug("[$idgraph] : Retornando grafico generado: ", $cache_new, 'logs_cache');
                    return $cache_new;
                } else {
                    $this->logs->error("[$idgraph] : Retornando error", null, 'logs_cache');
                    return false;
                }
            }
        }

        private function createGraph2($idgraph, $name, $query, $delay, $cache = true)
        {
            $this->logs->debug("[$idgraph] : Iniciando la obtencion de la data", null, 'logs_cache');

            $graph_dates = $this->conexion->queryFetch($query, 'logs_cache');

            if ($graph_dates && (count($graph_dates) > 0)) {

                $this->logs->debug("[$idgraph] : Query entrego un total de: ", count($graph_dates), 'logs_cache');

                $this->logs->debug("[$idgraph] : Query entrego : ", $graph_dates, 'logs_cache');

                foreach ($graph_dates as $key => $value) {
                    $graphOrder[$value['clock']] = $value['value'];
                    $history[] = $value['id_history'];
                }

                sort($history);

                ksort($graphOrder);

                $data = array();
                $finished = false;
                $ultimo = @end(array_keys($graphOrder));
                $ultimoHistory = end($history);
                $timeNow = time() * 1000;
                $anterior = current(array_keys($graphOrder));
                $delayMargen = $delay * 1300;
                $delay = $delay * 1000;

                $insert_cache = "INSERT INTO `bm_cache_graph` (`id_graph`, `fechahora`, `id_history`) VALUES ('$idgraph', NOW(), $ultimoHistory) ON DUPLICATE KEY UPDATE `id_history` = '$ultimoHistory';";

                $valida = $this->conexion->query($insert_cache, false, 'logs_cache');

                $GRAPH_null_DISPLAY = $this->parametro->get("GRAPH_NULL_DISPLAY", false);
                $GRAPH_null_DISPLAY_LIMIT = $this->parametro->get("GRAPH_NULL_DISPLAY_LIMIT", 100);
                $GRAPH_NOW_LIMIT = $this->parametro->get("GRAPH_NOW_LIMIT", false);
                
                foreach ($graphOrder as $key => $graph) {
                    if ($GRAPH_null_DISPLAY) {
                        $count = 0;
                        while (!$finished) :
                            if ($count <= $GRAPH_null_DISPLAY_LIMIT && ($anterior != $key) && ($key != $ultimo) && ($anterior + $delayMargen < $key)) {
                                $anterior = $anterior + $delay;
                                $data[] = "[" . $anterior . ",null] \n";
                            } elseif ($GRAPH_NOW_LIMIT && ($key == $ultimo) && $timeNow >= $anterior) {
                                $anterior = $anterior + $delay;
                                $data[] = "[" . $anterior . ",null] \n";
                            } else {
                                $finished = true;
                            }
                            $count++;
                        endwhile;

                        $finished = false;
                        $anterior = $key;
                    }

                    $data[] = "[" . $key . "," . $graph . "]\n";
                }
                $data_final = join($data, ',');

                $this->logs->debug("[$idgraph] : Datos contruidos: ", $data_final, 'logs_cache');

                if ($cache) {
                    $valida = $this->cacheWrite($idgraph, $data_final);
                } else {
                    $valida = $data_final;
                }
            } else {
                $this->logs->debug("[$idgraph] : Query sin datos ", $idgraph, 'logs_cache');
                if ($cache) {
                    $valida = $this->cache($idgraph, 0);
                } else {
                    $valida = false;
                }
            }

            if ($valida) {
                return '[' . $valida . ']';
            } else {
                return false;
            }
        }

        private function cache($id, $time = false)
        {

            $GRAPH_CACHE_TIME = $this->parametro->get("GRAPH_CACHE_TIME", 600);
            $GRAPH_CACHE_STATUS = $this->parametro->get("GRAPH_CACHE_STATUS_TIME", true);

            $this->cache_dir = SITE_PATH . 'cache/';

            if ($time !== false && ($time > 0)) {
                $this->logs->debug("[$id] : Validando si el cache cumple:", $time, 'logs_cache');
                $this->cache_time = $time;
            } elseif ($time !== false && ($time === 0)) {
                $this->logs->debug("[$id] : Cache sin limite de tiempo:", $GRAPH_CACHE_TIME, 'logs_cache');
                $GRAPH_CACHE_STATUS = false;
            } else {
                $this->logs->debug("[$id] : Utilizando parametro de tiempo:", $GRAPH_CACHE_TIME, 'logs_cache');
                $this->cache_time = $GRAPH_CACHE_TIME;
            }

            $this->file = $this->cache_dir . "cache_" . $id . "_graph.cache";
            //md5, encriptado por seguridad y para nombrar

            //condicional: Existencia del archivo, fecha expiración, acción
            if ($GRAPH_CACHE_STATUS && file_exists($this->file) && (fileatime($this->file) + $this->cache_time) > time()) {
                $this->logs->debug("[$id] : Entregando cache activo", null, 'logs_cache');
                $openCache = fopen($this->file, "r");
                $cache = fread($openCache, filesize($this->file));
                fclose($openCache);
                return $cache;
            } elseif (file_exists($this->file) && !$GRAPH_CACHE_STATUS) {
                $this->logs->debug("[$id] : Entregando cache disponible", null, 'logs_cache');
                $openCache = fopen($this->file, "r");
                $cache = fread($openCache, filesize($this->file));
                fclose($openCache);
                return $cache;
            } else {
                $this->logs->debug("[$id] : Cache no existe", null, 'logs_cache');
                $this->caching = true;
                return false;
            }
        }

        private function cacheWrite($id, $data_final)
        {
            if ($this->caching) {

                $this->file = $this->cache_dir . "cache_" . $id . "_graph.cache";

                if (file_exists($this->file)) {
                    $this->logs->debug("[$id] : Incrementando cache", null, 'logs_cache');
                    $fp = fopen($this->file, 'a');
                    $cache = fwrite($fp, "," . $data_final);
                    $contents = fread($fp, filesize($this->file)) . "," . $data_final;
                    fclose($fp);
                    return $contents;
                } else {
                    $this->logs->debug("[$id] : Generando cache:", null, 'logs_cache');
                    $fp = fopen($this->file, 'w+');
                    $cache = fwrite($fp, $data_final);
                    fclose($fp);
                    $this->logs->debug("[$id] : Retornando el cache:", $cache, 'logs_cache');
                    return $data_final;
                }

            }
        }

        public function getGraphList()
        {
            $get_graphs_sql = "SELECT `id_graph`,`name` FROM `bm_graphs` ORDER BY `name`";

            $get_graphs_result = $this->conexion->queryFetch($get_graphs_sql);

            if ($get_graphs_result) {
                return $get_graphs_result;
            } else {
                return false;
            }
        }

        public function getScreenList($groupid = 0)
        {
            $get_sql = "SELECT `screenid`,`name` FROM `bm_screens` WHERE `groupid` = $groupid ORDER BY `name`";

            $get_result = $this->conexion->queryFetch($get_sql);

            if ($get_result) {
                return $get_result;
            } else {
                return false;
            }
        }

        public function getGraph3($graphid, $host, $item, $iditem, $force, $limitAsig = 0, $delay = 6000)
        {
            $GRAPH_CACHE_STATUS = $this->parametro->get('GRAPH_CACHE_STATUS', true);

            $idgraph = $graphid . "_" . $host;

            if (!isset($limitAsig) || $limitAsig == 0 || (!is_numeric($limitAsig))) {
                $LIMIT = $this->parametro->get('LIMIT_GRAPH', 1000);
            } else {
                $LIMIT = $limitAsig;
            }

            $SELECT = "SELECT `clock`*1000 as clock,`value`, `id_history`,`id_item`,`id_host` FROM `bm_history`";

            if (is_array($host)) {
                $hostArray = true;
                $WHERE = " WHERE `id_host` IN " . $this->conexion->arrayIN($host) . " AND `id_item` IN $iditem";
            } else {
                $hostArray = false;
                $WHERE = " WHERE `id_host` = $host  AND `id_item` IN $iditem";
            }

            $ORDER = " ORDER BY `clock` DESC LIMIT $LIMIT;";

            $query = $SELECT . $WHERE . $ORDER;

            $this->logs->debug("Solicitando Graph: ", $query, 'logs_graph');

            //Valida Cache

            if ($GRAPH_CACHE_STATUS === true) {
                
                $cacheValida = $this->cache($idgraph);
                if($cacheValida){
                    $this->logs->debug("[$idgraph] : Retornando cache del grafico:", null, 'logs_cache');
                    return $cacheValida;
                }
            } 
        
            $this->logs->debug("[$idgraph] : Generando cache , dado que no cumple o no se encuentra", null, 'logs_cache');

            if ($hostArray == true) {
                $cache_new = $this->createGraph4($idgraph, $item, $query, $GRAPH_CACHE_STATUS);
            } else {
                $cache_new = $this->createGraph3($idgraph, $item, $query, $GRAPH_CACHE_STATUS, $hostArray);
            }

            if ($cache_new) {
                $this->logs->debug("[$idgraph] : Retornando grafico generado: ", $cache_new, 'logs_cache');
                return $cache_new;
            } else {
                $this->logs->error("[$idgraph] : Retornando error", null, 'logs_cache');
                return false;
            }
            
        }

        public function getGraphFilter($monitorid, $planid ,$hostid, $item , $force, $limitAsig = 0, $delay = 6000)
        {
            $GRAPH_CACHE_STATUS = $this->parametro->get('GRAPH_CACHE_STATUS', true);

            $idgraph = $monitorid . "_" . $item['id_item']. "_" . $planid;

            if (!isset($limitAsig) || $limitAsig == 0 || (!is_numeric($limitAsig))) {
                $LIMIT = $this->parametro->get('LIMIT_GRAPH', 1000);
            } else {
                $LIMIT = $limitAsig;
            }

            $SELECT = "SELECT `clock`*1000 as clock,`value`, `id_history`,`id_item`,`id_host` FROM `bm_history`";

            $WHERE = " WHERE `id_host` IN (" . $hostid . ") AND `id_item` = $monitorid";
    
            $ORDER = " ORDER BY `clock` DESC LIMIT $LIMIT;";

            $query = $SELECT . $WHERE . $ORDER;

            $this->logs->debug("Solicitando Graph: ", $query, 'logs_graph');

            //Valida Cache

            $cacheValida = $this->cache($idgraph);

            if ($GRAPH_CACHE_STATUS && $cacheValida) {

                $this->logs->debug("[$idgraph] : Retornando cache del grafico:", null, 'logs_cache');

                return $cacheValida;

            } else {

                $this->logs->debug("[$idgraph] : Generando cache , dado que no cumple o no se encuentra", null, 'logs_cache');

                $cache_new = $this->createGraphFilter($idgraph, $item, $query, $hostid ,$GRAPH_CACHE_STATUS);
               
                if ($cache_new) {
                    $this->logs->debug("[$idgraph] : Retornando grafico generado: ", $cache_new, 'logs_cache');
                    return $cache_new;
                } else {
                    $this->logs->error("[$idgraph] : Retornando error", null, 'logs_cache');
                    return false;
                }
            }
        }

        private function createGraph3($idgraph, $item, $query, $cache = false, $hostArray)
        {
            $this->logs->debug("[$idgraph] : Iniciando la obtencion de la data", null, 'logs_cache');

            $graph_dates = $this->conexion->queryFetch($query, 'logs_cache');
            
            if ($graph_dates && ($count = count($graph_dates) > 0)) {

                $this->logs->debug("[$idgraph] : Query entrego un total de: ", count($graph_dates), 'logs_cache');

                //$this->logs->debug("[$idgraph] : Query entrego :
                // ",$graph_dates,'logs_cache');
                    
                $graph_dates = array_reverse($graph_dates);
                $ultimoHistory = 0;
                foreach ($graph_dates as $key => $value) {
                    if ($hostArray) {
                        $graphOrder[$value['id_item'] . "_" . $value['id_host']][$value['clock']] = $value['value'];
                    } else {
                        $graphOrder[$value['id_item']][$value['clock']] = $value['value'];
                    }

                    if ($ultimoHistory < $value['id_history']) {
                        $ultimoHistory = $value['id_history'];
                    }
                }

                $insert_cache = "INSERT INTO `bm_cache_graph` (`id_graph`, `fechahora`, `id_history`) VALUES ('$idgraph', NOW(), $ultimoHistory) ON DUPLICATE KEY UPDATE `id_history` = '$ultimoHistory';";

                $valida = $this->conexion->query($insert_cache, false, 'logs_cache');

                $GRAPH_null_DISPLAY = $this->parametro->get("GRAPH_null_DISPLAY", false);
                $GRAPH_null_DISPLAY_LIMIT = $this->parametro->get("GRAPH_null_DISPLAY_LIMIT", 100);
                
                $GRAPH_DATE_LAST_POINT = $this->parametro->get("GRAPH_DATE_LAST_POINT", true);
                
                $GRAPH_NOW_LIMIT=$this->parametro->get("GRAPH_NOW_LIMIT",false);
                //$GRAPH_NOW_LIMIT = false;

                foreach ($graphOrder as $keyhistory => $valuehistory) {
                    $finished = false;
                    $anterior = current(array_keys($valuehistory));
                    $ultimo = @end(array_keys($valuehistory));

                    $timeNow = time() * 1000;

                    if ($hostArray) {
                        list($id_item, $id_host) = explode('_', $keyhistory);
                        $delayMargen = $item[$id_item]['delay'] * 1300;
                        $delay = $item[$id_item]['delay'] * 1000;
                    } else {
                        $delayMargen = $item[$keyhistory]['delay'] * 1300;
                        $delay = $item[$keyhistory]['delay'] * 1000;
                    }

                    $data = array();

                    foreach ($valuehistory as $key => $graph) {
                        if ($GRAPH_null_DISPLAY) {
                            $count = 0;
                            while (!$finished) :
                                if ($count <= $GRAPH_null_DISPLAY_LIMIT && ($anterior != $key) && ($key != $ultimo) && ($anterior + $delayMargen < $key)) {
                                    $anterior = $anterior + $delay;
                                    $data[] = "[" . $anterior . ",null]";
                                } elseif ($GRAPH_NOW_LIMIT && ($key == $ultimo) && $timeNow >= $anterior) {
                                    $anterior = $anterior + $delay;
                                    $data[] = "[" . $anterior . ",null]";
                                } else {
                                    $finished = true;
                                }
                                $count++;
                            endwhile;

                            $finished = false;
                            $anterior = $key;
                        }

                        //$data[] = array($key,$graph);
                        $data[] = "[" . $key . "," . $graph . "]";
                    }

                    if($GRAPH_DATE_LAST_POINT) {
                        $data[] = "[" . $timeNow . ",null]";
                    }

                    //$data_final[$keyhistory] = $data;

                    if ($hostArray) {
                        $data_fina[$id_host][] = '"' . $id_item . '": [' . join($data, ',') . ']';
                    } else {
                        $data_fina[] = '"' . $keyhistory . '": [' . join($data, ',') . ']';
                    }

                    //$data_final[$item[$keyhistory]['description']] = $data;
                }

                if ($hostArray) {
                    foreach ($data_fina as $key => $value) {
                        $data_final = "{\"$key\":{" . join($value, ',') . "}}";
                    }
                } else {
                    $data_final = "{" . join($data_fina, ',') . "}";
                }
                
           
                if ($cache == true) {
                    $valida = $this->cacheWrite($idgraph, $data_final);
                } else {
                    $valida = $data_final;
                }
            } else {
                $this->logs->debug("[$idgraph] : Query sin datos ", $idgraph, 'logs_cache');
                if ($cache) {
                    $valida = $this->cache($idgraph, 0);
                } else {
                    $valida = false;
                }
            }

            if ($valida) {
                
                
                
                return $valida;
            } else {
                return false;
            }
        }

        private function createGraph4($idgraph, $item, $query, $cache = false)
        {
            $this->logs->debug("[$idgraph] : Iniciando la obtencion de la data", null, 'logs_cache');

            $graph_dates = $this->conexion->queryFetch($query, 'logs_cache');

            if ($graph_dates && ($count = count($graph_dates) > 0)) {

                $this->logs->debug("[$idgraph] : Query entrego un total de: ", count($graph_dates), 'logs_cache');

                //$this->logs->debug("[$idgraph] : Query entrego :
                // ",$graph_dates,'logs_cache');

                $ultimoHistory = 0;

                foreach ($graph_dates as $key => $value) {
                    $graphOrder[$value['id_item']][$value['id_host']][$value['clock']] = $value['value'];
                    if ($ultimoHistory < $value['id_history']) {
                        $ultimoHistory = $value['id_history'];
                    }
                }

                $insert_cache = "INSERT INTO `bm_cache_graph` (`id_graph`, `fechahora`, `id_history`) VALUES ('$idgraph', NOW(), $ultimoHistory) ON DUPLICATE KEY UPDATE `id_history` = '$ultimoHistory';";

                $valida = $this->conexion->query($insert_cache, false, 'logs_cache');

                $GRAPH_null_DISPLAY = $this->parametro->get("GRAPH_null_DISPLAY", false);
                $GRAPH_null_DISPLAY_LIMIT = $this->parametro->get("GRAPH_null_DISPLAY_LIMIT", 100);
                $GRAPH_NOW_LIMIT=$this->parametro->get("GRAPH_NOW_LIMIT",false);
                $GRAPH_DATE_LAST_POINT = $this->parametro->get("GRAPH_DATE_LAST_POINT", true);
                //$GRAPH_NOW_LIMIT = false;

                foreach ($graphOrder as $keyHost => $host) {
                    foreach ($host as $keyhistory => $valuehistory) {

                        ksort($valuehistory);
                        $finished = false;
                        $anterior = current(array_keys($valuehistory));
                        $ultimo = @end(array_keys($valuehistory));
                        $timeNow = time() * 1000;

                        $delayMargen = $item[$keyhistory]['delay'] * 1300;
                        $delay = $item[$keyhistory]['delay'] * 1000;

                        $data = array();

                        foreach ($valuehistory as $key => $graph) {
                            if ($GRAPH_null_DISPLAY) {
                                $count = 0;
                                while (!$finished) :
                                    if ($count <= $GRAPH_null_DISPLAY_LIMIT && ($anterior != $key) && ($key != $ultimo) && ($anterior + $delayMargen < $key)) {
                                        $anterior = $anterior + $delay;
                                        $data[] = "[" . $anterior . ",null]";
                                    } elseif ($GRAPH_NOW_LIMIT && ($key == $ultimo) && $timeNow >= $anterior) {
                                        $anterior = $anterior + $delay;
                                        $data[] = "[" . $anterior . ",null]";
                                    } else {
                                        $finished = true;
                                    }
                                    $count++;
                                endwhile;

                                $finished = false;
                                $anterior = $key;
                            }

                            $data[] = "[" . $key . "," . $graph . "]";
                        }

                        $item_value[] = '"' . $keyhistory . '": [' . join($data, ',') . ']';
                    }
                    $data_group_host[] = "\"$keyHost\":{" . join($item_value, ',') . "}";
                }

                $data_final = "{" . join($data_group_host, ',') . "}";
                


                if ($cache) {
                    $valida = $this->cacheWrite($idgraph, $data_final);
                } else {
                    $valida = $data_final;
                }
            } else {
                $this->logs->debug("[$idgraph] : Query sin datos ", $idgraph, 'logs_cache');
                if ($cache) {
                    $valida = $this->cache($idgraph, 0);
                } else {
                    $valida = false;
                }
            }

            if ($valida) {
                return $valida;
            } else {
                return false;
            }
        }

        private function createGraphFilter($idgraph, $item, $query, $hostid , $cache = false)
        {
            
            $this->logs->debug("[$idgraph] : Iniciando la obtencion de la data", null, 'logs_cache');

            $graph_dates = $this->conexion->queryFetch($query, 'logs_cache');

            if ($graph_dates && ($count = count($graph_dates) > 0)) {

                $this->logs->debug("[$idgraph] : Query entrego un total de: ", count($graph_dates), 'logs_cache');

                //$this->logs->debug("[$idgraph] : Query entrego :
                // ",$graph_dates,'logs_cache');

                $ultimoHistory = 0;

                foreach ($graph_dates as $key => $value) {
                    $graphOrder[$value['id_host']][$value['clock']] = $value['value'];
                    if ($ultimoHistory < $value['id_history']) {
                        $ultimoHistory = $value['id_history'];
                    }
                }
                
                $insert_cache = "INSERT INTO `bm_cache_graph` (`id_graph`, `fechahora`, `id_history`) VALUES ('$idgraph', NOW(), $ultimoHistory) ON DUPLICATE KEY UPDATE `id_history` = '$ultimoHistory';";

                $valida = $this->conexion->query($insert_cache, false, 'logs_cache');

                $GRAPH_null_DISPLAY = $this->parametro->get("GRAPH_null_DISPLAY", false);
                $GRAPH_null_DISPLAY_LIMIT = $this->parametro->get("GRAPH_null_DISPLAY_LIMIT", 100);
                //$GRAPH_NOW_LIMIT=$this->parametro->get("GRAPH_NOW_LIMIT",false);
                $GRAPH_NOW_LIMIT = false;
                
                
                foreach ($graphOrder as $hostid => $clock) {
                    
                    ksort($clock);
                    
                    foreach ($clock as $key => $value) {
                        $data[] = "[" . $key . "," . $value . "]";
                    }
                    
                    $gorupHost[$hostid] = $data; 
                }

                foreach ($gorupHost as $hostid => $data) {
                    $groupData[] = '"' . $hostid . '": [' .  join(',', $data)  . "]";
                }
                
                
                $valida = "{".join(',', $groupData)."}";
               

            } else {
                $this->logs->debug("[$idgraph] : Query sin datos ", $idgraph, 'logs_cache');
                if ($cache) {
                    $valida = $this->cache($idgraph, 0);
                } else {
                    $valida = false;
                }
            }

            if ($valida) {
                return $valida;
            } else {
                return false;
            }
        }
    }

}
?>