<?php
    class utility  {
        
        function __construct($value, $option = false) {
            $this->value = $value;
            $this->option = $option;
            if($value === false){
                return false;
            }
        }
        
        public function formatINI($alwayNumeric = false)
        {
           
            if (($this->value) && is_array($this->value)) {

                if($this->option != false ) {
                    $valueName = $this->option['key'];
                } else {
                    $valueName = false;
                }
            
                $datosArray = array();

                foreach ($this->value as $key => $value) {
                    if ($valueName === false) {
                        if ($alwayNumeric) {
                            if (is_numeric($value)) {
                                $datosArray[] = $value;
                            }
                        } else {
                            $datosArray[] = $value;
                        }
                    } else {
                        if ($alwayNumeric) {
                            if (is_numeric($value[$valueName])) {
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

        public function formatArray($selected)
        {
            if (($this->value) && is_array($this->value)) {
                return $this->value;
            } else {
                return false;
            }
        }
        
        public function formatOption($selected = false)
        {
            if (($this->value) && is_array($this->value)) {
                
                if($this->option != false  && isset($this->option["key"]) && isset($this->option["value"])) {
                    $keyName = $this->option["key"];
                    $valueName = $this->option["value"];
                } else {
                    return false;
                }
                
                foreach ($this->value as $key => $value) {
                    if($selected != $value[$keyName]) {
                        $selectedOption = '';
                    } else {
                        $selectedOption = 'selected';
                    }
                    $option[] = "<option $selectedOption value=\"" . $value[$keyName] . '">' . $value[$valueName] . '</option>';
                }
                
                
                return join("\n", $option);
            } else {
                return false;
            }
        }
        
        public function firstValue()
        {
            if (($this->value) && is_array($this->value)) {
                if($this->option != false  && isset($this->option["key"]) && isset($this->option["value"])) {
                    $keyName = $this->option["key"];
                    $valueName = $this->option["value"];
                    
                    return $this->value[0][$valueName];
                } else {
                    return false;
                }   
            }  else {
                return false;
            }       
        }
      
        public function firstKey()
        {
            if (($this->value) && is_array($this->value)) {
                if($this->option != false  && isset($this->option["key"]) && isset($this->option["value"])) {
                    $keyName = $this->option["key"];
                    $valueName = $this->option["value"];
                    
                    return $this->value[0][$keyName];
                } else {
                    return false;
                }   
            }  else {
                return false;
            }       
        }
    }
            
	if( ! class_exists("bmonitor")) {
		class Bmonitor {

			function __construct( $parametro, $conexion, $logs, $language, $protect, $basic, $plantilla ) {
				$this->parametro = $parametro;
				$this->conexion = $conexion;
				$this->logs = $logs;
				$this->language = $language;
				$this->protect = $protect;
				$this->basic = $basic;
				$this->plantilla = $plantilla;
			}

            //START VERSION 2
            
            public function getAllGroups($filterTypeGroupsStatus = false)
            {
                $userGroupID = $this->protect->getGroupUser();
                $isAdmin = $this->protect->is_admin();
                
                if($userGroupID) {
                    
                    if($filterTypeGroupsStatus){
                        $filterTypeGroups = "AND HG.`type` = 'QoS'";
                    } else {
                        $filterTypeGroups = '';
                    }

                    if(!$isAdmin){
                        $filterGroupUser = "AND UGHG.`id_group` = $userGroupID";
                    } else {
                        $filterGroupUser = '';
                    }
                                        
                    $getGroupsListSQL = "SELECT DISTINCT HG.`groupid`, HG.`name` 
                                FROM `bm_host_groups` HG 
                                    LEFT OUTER JOIN `bm_user_host_group` UGHG ON UGHG.`groupid`= HG.`groupid`
                                    WHERE 
                                        HG.`borrado` = 0 AND 
                                        HG.`name` != ''
                                        $filterGroupUser
                                        $filterTypeGroups
                                    ORDER BY HG.`name`;";
                    //$this->logs->error("Los grupos a que se pueden editar son: ".$getGroupsListSQL);             
                    $getGroupsListRESULT = $this->conexion->queryFetch($getGroupsListSQL);
                    
                    if($getGroupsListRESULT) {
                        return new utility($getGroupsListRESULT, array("key" => "groupid","value" => 'name'));
                    } else {
                        return new utility(false);
                    }
                    
                } else {
                    return new utility(false);
                }
                
            }

            public function getAllGroupsUser()
            {
                
            }
            
            public function getAllItems($groupid = false){
            		
				if(is_numeric($groupid)){
					$groupid = "($groupid)";
				} elseif (is_object($groupid)) {
                    $groupid = $groupid->formatINI();
                } else {
                	$groupid = $this->getAllGroups()->formatINI();
                }
                
                $getItemsSQL = "SELECT DISTINCT I.`id_item`, I.`name`, I.`descriptionLong`   
                        FROM `bm_items` I 
                        INNER JOIN `bm_items_groups` IG ON I.`id_item`=IG.`id_item`
                        LEFT OUTER JOIN `bm_host_groups` HG ON HG.`groupid`=IG.`groupid`
                        WHERE IG.`groupid` IN $groupid
                        ORDER BY I.`descriptionLong`";
                        
                $getItemsRESULT = $this->conexion->queryFetch($getItemsSQL);
                
                if($getItemsRESULT) {
                    return new utility($getItemsRESULT, array("key" => "id_item","value" => 'descriptionLong'));
                } else {
                    return new utility(false);
                }
            }

            public function getAllPlans($groupid = false){
            		
				if(is_numeric($groupid)){
					$groupid = "($groupid)";
				} elseif (is_object($groupid)) {
                    $groupid = $groupid->formatINI();
                } else {
                	$groupid = $this->getAllGroups()->formatINI();
                }
                
                $getItemsSQL = "SELECT  P.`id_plan`, P.`plan`, PG.`groupid`  
				   	FROM `bm_plan` P 
				   		LEFT JOIN `bm_plan_groups` PG ON PG.`id_plan`=P.`id_plan`
				   	WHERE PG.`borrado` = 0 AND `groupid` IN $groupid
				   	GROUP BY P.`id_plan`";
                        
                $getItemsRESULT = $this->conexion->queryFetch($getItemsSQL);
                
                if($getItemsRESULT) {
                    return new utility($getItemsRESULT, array("key" => "id_plan","value" => 'plan'));
                } else {
                    return new utility(false);
                }
            }

            public function getAllAgents($groupid = false){
            		
				if(is_numeric($groupid)){
					$groupid = "($groupid)";
				} elseif (is_object($groupid)) {
                    $groupid = $groupid->formatINI();
                } else {
                	$groupid = $this->getAllGroups()->formatINI();
                }
                
                $getItemsSQL = "SELECT `id_host`, `host`  
				   	FROM `bm_host` H
				   	WHERE `borrado` = 0 AND `groupid` IN $groupid";
                        
                $getItemsRESULT = $this->conexion->queryFetch($getItemsSQL);
                
                if($getItemsRESULT) {
                    return new utility($getItemsRESULT, array("key" => "id_host","value" => 'host'));
                } else {
                    return new utility(false);
                }
            }
						
            public function getHostItems($idHost)
            {
                if(!is_numeric($idHost)){
                    return new utility(false);
                }
                
                $getHostItemsSQL = "SELECT I.`id_item`,I.`name`,I.`descriptionLong`
                        FROM `bm_items` I
                            LEFT JOIN `bm_items_groups` IG ON I.`id_item`=IG.`id_item`
                            LEFT JOIN `bm_host` H ON H.`groupid`=IG.`groupid`
                    WHERE H.`id_host` = $idHost AND H.`borrado`=0;";
                    
                $getHostItemsRESULT = $this->conexion->queryFetch($getHostItemsSQL);
                
                if($getHostItemsRESULT) {
                    return new utility($getHostItemsRESULT, array("key" => "id_item","value" => 'descriptionLong'));
                } else {
                    return new utility(false);
                }
            }

           
            //END VERSION 2

			public function unsetVarCache( ) {
				if(isset($_SESSION['getAllGroupsHost'])) {
					unset($_SESSION['getAllGroupsHost']);
				}
			}

			public function getGroupsHost( $IN_SQL = false, $format_in = false, $category = false, $op = false ) {

				$groups = $this->getAllGroupsHost($category, false, $op);

				if($groups) {
					if($IN_SQL) {
						if(is_array($groups)) {
							$group_in = array();
							foreach ($groups as $key => $group) {
								$group_in[] = $group['groupid'];
							}
							if($format_in) {
								return $this->conexion->arrayToIN($group_in);
							} else {
								return $group_in;
							}
						} else {
							return false;
						}
					} else {
						return $groups;
					}
				} else {
					return false;
				}
			}

			public function getAllGroupsHost( $category = false, $formatRespond = false, $op = false ) {

				if(isset($_SESSION['getAllGroupsHost'])) {
					if($formatRespond === false) {
						if(isset($_SESSION['getAllGroupsHost']['false'])) {
							return $_SESSION['getAllGroupsHost']['false'];
						}
					} elseif($formatRespond === 'json') {
						if(isset($_SESSION['getAllGroupsHost']['json'])) {
							header('Content-Type: application/json');
							echo $_SESSION['getAllGroupsHost']['json'];
							exit ;
						}
					} elseif($formatRespond === 'option') {
						if(isset($_SESSION['getAllGroupsHost']["option" .$op])) {
							return $_SESSION['getAllGroupsHost']["option" .$op];
						}
					} elseif($formatRespond === 'sql') {
						if(isset($_SESSION['getAllGroupsHost']["arrayToIN"])) {
							return $_SESSION['getAllGroupsHost']["arrayToIN"];
						}
					}
				}

				$where = "";

				$isAdmin = $this->protect->is_admin();

				$GroupUser = $this->protect->getGroupUser();

				$getGroupSQL = "SELECT hg.`groupid`, hg.`name` ,  IF(uhg.`id_group` IS NULL, 0, 1) as selected
                                FROM `bm_host_groups` hg 
                                    LEFT OUTER JOIN `bm_user_host_group` uhg ON uhg.`groupid`= hg.`groupid`
                                    WHERE 
                                        hg.`borrado` = 0 AND 
                                        hg.`name` != ''";
				$WHERE = '';
				if($isAdmin !== true) {
					if($GroupUser == '') {
						return false;
					}

					$WHERE = " AND uhg.`id_group` IN ($GroupUser) ";

				}

				if($category) {
					$WHERE .= " AND hg.`type` = '$category' ";
				}

				$getGroupSQL = $getGroupSQL .$WHERE ." GROUP BY hg.`groupid` ORDER BY hg.`name`";

				//$this->logs->error("Consulta todos los grupos core/basic/libs.bmonitor.php : ".$getGroupSQL);
				$groups = $this->conexion->queryFetch($getGroupSQL); 

				if($groups) {
					if($formatRespond === false) {
						$_SESSION['getAllGroupsHost']['false'] = $groups;
						return $groups;
					} elseif($formatRespond === 'json') {
						$return = json_encode($groups);
						$_SESSION['getAllGroupsHost']['json'] = $return;
						header('Content-Type: application/json');
						echo $return;
						exit ;
					} elseif($formatRespond === 'option') {
						$return = $this->basic->getOption($groups, 'groupid', 'name', $op);
						$_SESSION['getAllGroupsHost']["option" .$op] = $return;
						return $return;
					} elseif($formatRespond === 'sql') {
						$return = $this->conexion->arrayToIN($groups, 'groupid');
						$_SESSION['getAllGroupsHost']["arrayToIN"] = $return;
						return $return;
					} else {
						return $groups;
					}
				} else {
					$array['error'] = $this->language->ITERNAL_ERROR;

					if($formatRespond === false) {
						return $array['error'];
					} elseif($formatRespond === 'json') {
						header('Content-Type: application/json');
						echo json_encode($array);
						exit ;
					} elseif($formatRespond === 'option') {
						return '<option value="00">' .$array['error'] .'</option>';
					} else {
						return $array['error'];
					}
					return false;
				}
			}

			public function getTypeGroups( ) {
				$is_admin = $this->protect->is_admin();

				if($is_admin) {
					return array(
						"code" => 1,
						"list" => array(
							'QoS',
							'QoE',
							'ENLACES',
							'NEUTRALIDAD'
						)
					);
				} else {

					$GroupUser = $this->protect->getGroupUser();

					$getGroupTypeSQL = "SELECT DISTINCT hg.`type`
                                    FROM `bm_host_groups` hg 
                                        LEFT OUTER JOIN `bm_user_host_group` uhg ON uhg.`groupid`= hg.`groupid`
                                    WHERE 
                                        hg.`borrado` = 0 AND 
                                        hg.`name` != '' AND 
                                        uhg.`id_group` IN ($GroupUser)";

					$getGroupTypeRESULT = $this->conexion->queryFetch($getGroupTypeSQL);

					if($getGroupTypeRESULT) {
						foreach ($getGroupTypeRESULT as $key => $value) {
							$return['list'][] = $value['type'];
						}

						$QoS = (array_search('QoS', $return['list']) !== false) ? true : false;
						$QoE = (array_search('QoE', $return['list']) !== false) ? true : false;
						$ENLACES = (array_search('ENLACES', $return['list']) !== false) ? true : false;
						$NEUTRALIDAD = (array_search('NEUTRALIDAD', $return['list']) !== false) ? true : false;
			
						if($QoS === true || $ENLACES === true || $NEUTRALIDAD == true){
							$return['code'] = 1;
						} else {
							$return['code'] = 2;
						}
												
						return $return;
					} else {
						return array("code" => 0);
					}
				}

			}

			public function getHostForGroup( $groupid, $type = false, $inactiveDisplay = false, $idplan = false ) {
				$category = false;
				if(isset($groupid)) {
					if( ! is_numeric($groupid)) {
						$category = $groupid;
					}
				} elseif(isset($_POST['groupid'])) {
					if( ! is_numeric($_POST['groupid'])) {
						return false;
					} else {
						$groupid = $_POST['groupid'];
					}
				}

				$getgroups = $this->getGroupsHost(true, true, $category);

				$status = ' AND `status`=' .$this->parametro->get('HOST_STATUS_MONITORED', 1);

				if($inactiveDisplay) {
					$status = "";
				}

				$filterPlan = '';

				if($idplan != false) {
					if(is_array($idplan)) {
						$filterPlan = " AND `bm_host`.`id_plan` IN (" .join(',', $idplan) .")";
					} elseif(is_numeric($idplan)) {
						$filterPlan = " AND `bm_host`.`id_plan`=" .$idplan;
					}
				}

				if( ! isset($getgroups) || $getgroups == '') {
					return false;
				}

				if($category) {
					$sql = 'SELECT `id_host`,`host` FROM `bm_host`
					WHERE `bm_host`.`borrado`=0  AND `groupid` IN ' .$getgroups .$status .$filterPlan ." ORDER BY `host`";
				} else {
					$sql = 'SELECT `id_host`,`host` FROM `bm_host` WHERE `bm_host`.`borrado`=0  AND `groupid` IN ' .$getgroups .' AND `groupid`=' .$groupid .$status .$filterPlan ." ORDER BY `host`";
				}

				$host = $this->conexion->queryFetch($sql);
				//$this->logs->error("ola 2da consulta.  $sql"); 
				if($host) {

					if($type === false) {
						return $host;
					} elseif($type === true) {
						header('Content-Type: application/json');
						echo json_encode($host);
						exit ;
					} elseif($type === 'option') {
						return $this->basic->getOption($host, 'id_host', 'host', 'all');
					} else {
						return $host;
					}

				} else {

					return false;
				}
			}

			public function getAllPlan( $formatRespond = false, $selected = false, $plan_id = false, $type = 'NEUTRALIDAD', $idhost = false ) {
				$select = "SELECT DISTINCT P.`id_plan`, P.`plan` FROM `bm_plan` P 
                        LEFT JOIN `bm_plan_groups` PG USING(`id_plan`) 
                        LEFT JOIN `bm_host_groups` HG ON HG.`groupid`=PG.`groupid` 
                        LEFT JOIN `bm_host` H ON H.`id_plan`=P.`id_plan`
                        WHERE HG.`type`='$type' AND PG.`borrado`=0";

				if((int)$plan_id > 0) {
					$select = $select ." AND `id_plan`=" .$plan_id;
				}

				if(($idhost) && (int)$idhost > 0) {
					$select = $select ." AND H.`id_host`=" .$idhost;
				}

				$plan = $this->conexion->queryFetch($select);

				if($plan) {
					if($formatRespond === false) {
						return $plan;
					} elseif($formatRespond === 'json') {
						header('Content-Type: application/json');
						echo json_encode($plan);
						exit ;
					} elseif($formatRespond === 'option') {
						return $this->basic->getOption($plan, 'id_plan', 'plan', $selected);
					} elseif($formatRespond === 'sql') {
						return $this->conexion->arrayToIN($plan, 'id_plan');
					} else {
						return $plan;
					}
				} else {
					$array['error'] = $this->language->ITERNAL_ERROR;

					if($formatRespond === false) {
						return $array['error'];
					} elseif($formatRespond === 'json') {
						header('Content-Type: application/json');
						echo json_encode($array);
						exit ;
					} elseif($formatRespond === 'option') {
						return '<option value="00">' .$array['error'] .'</option>';
					} else {
						return $array['error'];
					}
					return false;
				}
			}

			public function getSchool( $formatRespond = false, $region = false ) {
				$select_sql = "SELECT `id_colegio`, 
                                    CONCAT(UCASE(C.`COMUNA_NOMBRE`) , ' - ', `establecimiento` , 
                                    ' [' ,  `direccion` , ']') As establecimiento  FROM `bm_colegios` CO 
                                    LEFT JOIN `bm_comuna` C ON C.`COMUNA_ID`=CO.`COMUNA_ID`";

				if($region) {
					$filter = ' WHERE CO.`REGION_ID`=' .$region;
				} else {
					$filter = '';
				}
				$select_result = $this->conexion->queryFetch($select_sql .$filter);

				if($select_result) {
					if($formatRespond === false) {
						return $select_result;
					} elseif($formatRespond === 'json') {
						header('Content-Type: application/json');
						echo json_encode($select_result);
						exit ;
					} elseif($formatRespond === 'option') {
						return $this->basic->getOption($select_result, 'id_colegio', 'establecimiento');
					} elseif($formatRespond === 'sql') {
						return $this->conexion->arrayToIN($select_result, 'id_colegio');
					} else {
						return $groups;
					}
				} else {
					$array['error'] = $this->language->ITERNAL_ERROR;

					if($formatRespond === false) {
						return $array['error'];
					} elseif($formatRespond === 'json') {
						header('Content-Type: application/json');
						echo json_encode($array);
						exit ;
					} elseif($formatRespond === 'option') {
						return '<option value="00">' .$array['error'] .'</option>';
					} else {
						return $array['error'];
					}
					return false;
				}

			}

			public function getAllHost( $IN_SQL = false, $format_in = false, $groupid = false ) {
				$groupHostIN = $this->getGroupsHost(true, true);

				if($groupid && (is_numeric($groupid))) {
					$FILTER_GROUP = " AND `groupid` = $groupid";
				} else {
					$FILTER_GROUP = '';
				}

				if($groupHostIN == false || $groupHostIN == '') {
					return false;
				}

				$sql = 'SELECT `id_host`,`host` FROM `bm_host`
					WHERE `bm_host`.`borrado`=0 AND `groupid` IN ' .$groupHostIN .$FILTER_GROUP .'  
					AND `status`=' .$this->parametro->get('HOST_STATUS_MONITORED', 1);

			//	echo $sql;
				
				$hosts = $this->conexion->queryFetch($sql);

				if($hosts) {
					if($IN_SQL) {
						$hosts_in = array();
						foreach ($hosts as $key => $host) {
							$hosts_in[] = $host['id_host'];
						}
						if($format_in) {
							return $this->conexion->arrayToIN($hosts_in);
						} else {
							return $hosts_in;
						}
					} else {
						return $hosts;
					}
				} else {
					return false;
				}
			}

			public function getPlanes( $group = false, $option = false, $json = false, $op = false, $msg = false ) {
				$continue = true;
				if($group) {
					if(is_array($group)) {
						$group_filter = $this->conexion->arrayToIN($group, 'groupid');
						$group_filter = " AND H.`groupid` IN " .$group_filter;
					} elseif(is_numeric($group)) {
						$group_filter = " AND H.`groupid` = " .$group;
					} else {
						$continue = false;
					}
				} else {
					$group_filter = '';
				}

				if($continue) {
					$get_planes_sql = "SELECT P.`id_plan`,`plan` , COUNT(H.`id_host`) as countHost FROM `bm_plan` P
												LEFT JOIN `bm_plan_groups` PG USING(`id_plan`) 
												LEFT JOIN `bm_host` H ON H.`id_plan`=P.`id_plan`
											WHERE PG.`borrado` = 0 AND H.`borrado`=0 $group_filter
											GROUP BY P.`id_plan` ";

					$get_planes_result = $this->conexion->queryFetch($get_planes_sql);

					if($get_planes_result) {
						if($option) {
							$return['datos'] = $this->basic->getOption($get_planes_result, 'id_plan', 'plan', $op, $msg);
						} else {
							$return['datos'] = $get_planes_result;
						}
					} else {
						$continue = false;
					}
				}

				if($json) {
					if($continue) {
						$return['status'] = true;
						header('Content-Type: application/json');
						echo json_encode($return);
						exit ;
					} else {
						$return['status'] = false;
						header('Content-Type: application/json');
						echo json_encode($return);
					}
				} else {
					if($continue) {
						return $return['datos'];
					} else {
						return false;
					}
				}
			}

			public function getItems( $getOption = false, $returnJson = false, $op = false, $typeGroups = false ) {
			    
                if($typeGroups !== false && $typeGroups != ''){
                    $filterTypeGroups = "AND HG.`type` = '$typeGroups'";
                } else {
                    $filterTypeGroups = '';
                }
                
				$getItemsSQL = "SELECT DISTINCT I.`id_item`, I.`name`, I.`descriptionLong`   
                        FROM `bm_items` I 
                        INNER JOIN `bm_items_groups` IG ON I.`id_item`=IG.`id_item`
                        LEFT OUTER JOIN `bm_host_groups` HG ON HG.`groupid`=IG.`groupid`
                        WHERE IG.`groupid` IN " .$this->getAllGroupsHost(false, 'sql') ." $filterTypeGroups
                        ORDER BY I.`descriptionLong`";

				$getItemsRESULT = $this->conexion->queryFetch($getItemsSQL);

				if($getItemsRESULT) {
                    
					if($getOption === true) {
					    $return =  $this->basic->getOption($getItemsRESULT, 'id_item', 'descriptionLong', $op);						
					} else {
					    $return = $getItemsRESULT;
					}
                    
                    if($returnJson){
                        $return['option'] = $return;
                        $return['status'] = false;
                        header('Content-Type: application/json');
                        echo json_encode($return);
                        exit;
                    } else {
                        return $return;
                    }

				} else {
					if($returnJson === false) {
						return false;
					} else {
						$return['status'] = false;
						header('Content-Type: application/json');
						echo json_encode($return);
						exit ;
					}
				}
			}

			//Depreacate
			public function getItemMonitor( $group = false, $option = false, $json = false, $op = false ) {
				$continue = true;
				if($group) {
					if(is_array($group)) {
						$group_filter = $this->conexion->arrayToIN($group, 'groupid');
						$group_filter = "WHERE `groupid` IN " .$group_filter;
					} elseif(is_numeric($group)) {
						$group_filter = "WHERE `groupid` = " .$group;
					} elseif($group == 'permit') {
						$group_filter = "WHERE `groupid` IN " .$this->getAllGroupsHost(false, 'sql');
					} else {
						$continue = false;
					}
				} else {
					$group_filter = '';
				}

				if($continue) {
					$get_planes_sql = 'SELECT DISTINCT I.`id_item`, I.`name`, I.`description` FROM `bm_items` I 
                    LEFT JOIN `bm_items_groups` USING(`id_item`)' .$group_filter .'ORDER BY I.`description`';

					$get_planes_result = $this->conexion->queryFetch($get_planes_sql);

					if($get_planes_result) {
						if($option) {
							$return['datos'] = $this->basic->getOption($get_planes_result, 'id_item', 'description', $op);
						} else {
							$return['datos'] = $get_planes_result;
						}
					} else {
						$continue = false;
					}
				}

				if($json) {
					if($continue) {
						$return['status'] = true;
						header('Content-Type: application/json');
						echo json_encode($return);
						exit ;
					} else {
						$return['status'] = false;
						header('Content-Type: application/json');
						echo json_encode($return);
					}
				} else {
					if($continue) {
						return $return['datos'];
					} else {
						return false;
					}
				}

			}

			public function getMenu( $active = false ) {

				$this->plantilla->load("menu", 'sitio/vista/');

				$tps_index_control = array();

				$valida = $this->protect->access_page('INDEX');

				if($valida) {
					$count = 1;
					if($active == 'monitor') {
						$class = 'active';
					} else {
						$class = '';
					}
					$tps_index_control['menu_permitido'] = "<li class='tabsMenu'><a class='$class' href='" .URL_BASE ."'>Monitorización</a></li>";
				}

				$valida = $this->protect->access_page('INVENTARIO');

				if($valida) {
					$count = 1;
					if($active == 'inventario') {
						$class = 'active';
					} else {
						$class = '';
					}

					if($count === 1) {
						$tps_index_control['menu_permitido'] .= '<li class="sep tabsMenu">|</li>';
						$tps_index_control['menu_permitido'] .= "<li class='tabsMenu'><a class='$class' href='" .URL_BASE ."menu/inventario'>Inventario</a></li>";
					} else {
						$tps_index_control['menu_permitido'] = "<li class='tabsMenu'><a class='$class' href='" .URL_BASE ."menu/inventario'>Inventario</a></li>";
					}
				}

				$valida = $this->protect->access_page('FDT');

				if($valida) {
					$count = 1;
					if($active == 'fdt') {
						$class = 'active';
					} else {
						$class = '';
					}
					if($count === 1) {
						$tps_index_control['menu_permitido'] .= '<li class="sep tabsMenu">|</li>';
						$tps_index_control['menu_permitido'] .= "<li class='tabsMenu'><a class='$class' href='" .URL_BASE ."menu/fdt'>FDT</a></li>";
					} else {
						$tps_index_control['menu_permitido'] = "<li class='tabsMenu'><a class='$class' href='" .URL_BASE ."menu/fdt'>FDT</a></li>";
					}
				}

				$valida = $this->protect->access_page('NEUTRALIDAD');

				if($valida) {
					$count = 1;
					if($active == 'neutralidad') {
						$class = 'active';
					} else {
						$class = '';
					}
					if($count === 1) {
						$tps_index_control['menu_permitido'] .= '<li class="sep tabsMenu">|</li>';
						$tps_index_control['menu_permitido'] .= "<li class='tabsMenu'><a class='$class' href='" .URL_BASE ."menu/neutralidad'>Neutralidad</a></li>";
					} else {
						$tps_index_control['menu_permitido'] = "<li class='tabsMenu'><a class='$class' href='" .URL_BASE ."menu/neutralidad'>Neutralidad</a></li>";
					}
				}

				$valida = $this->protect->access_page('CONFIGURACION');

				if($valida) {
					$count = 1;
					if($active == 'configuracion') {
						$class = 'active';
					} else {
						$class = '';
					}
					if($count === 1) {
						$tps_index_control['menu_permitido'] .= '<li class="sep tabsMenu">|</li>';
						$tps_index_control['menu_permitido'] .= "<li class='tabsMenu'><a class='$class' href='" .URL_BASE ."menu/configuracion'>Configuración</a></li>";
					} else {
						$tps_index_control['menu_permitido'] = "<li class='tabsMenu'><a class='$class' href='" .URL_BASE ."menu/configuracion'>Configuración</a></li>";
					}
				}

				$valida = $this->protect->access_page('ADMINISTRACION');

				if($valida) {
					if($active == 'administracion') {
						$class = 'active';
					} else {
						$class = '';
					}
					$count = 1;
					if($count === 1) {
						$tps_index_control['menu_permitido'] .= '<li class="sep tabsMenu">|</li>';
						$tps_index_control['menu_permitido'] .= "<li class='tabsMenu'><a class='$class' href='" .URL_BASE ."menu/administracion'>Administración</a></li>";
					} else {
						$tps_index_control['menu_permitido'] = "<li class='tabsMenu'><a class='$class' href='" .URL_BASE ."menu/administracion'>Administración</a></li>";
					}
				}

				$this->plantilla->set($tps_index_control);
				return $this->plantilla->get();
			}

			public function getTabs( $tabs, $tabsID = "tabs" ) {
				if(is_array($tabs)) {

					$result_tabs = '<div id="' .$tabsID .'"><ul>' ."\n";
					foreach ($tabs as $key => $tab) {
						$valida = true;

						if(isset($tab['protec'])) {

							if(is_array($tab['protec'])) {
								$valida = $this->protect->access_page($tab['protec']['code'], true, $tab['protec']['title'], $tab['protec']['description']);
							} else {
								$valida = $this->protect->access_page($tab['protec']);
							}

						}

						if($valida && (isset($tab['href'])) && (isset($tab['title']))) {

							if(isset($tab['urlBase']) && $tab['urlBase'] === true) {
								$href = '/' .$tab['href'];
							} else {
								$href = $tab['href'];
							}

							$result_tabs .= '<li><a href="' .$href .'">' .$tab['title'] .'</a></li>' ."\n";

						}
					}
					$result_tabs .= '</ul></div>';

					return $result_tabs;
				} else {
					return false;
				}
			}

		}

	}
?>