<?php
if (!class_exists("Control")) {
	class Control {

		public function __construct($slave = false, $domain = false, $server = false) {
			$parametro = new Parametros;

			if ($domain == false) {

				if (defined('URL_BASE_FULL')) {
					$urlBase = explode('/', URL_BASE_FULL);

					$urlBase = $urlBase[0];
				} else {
					$urlBase = 'none';
				}

			} else {
				$urlBase = $domain;
			}
			$lang = false;
			if (file_exists(ETC . 'company.inc.php')) {
				require ETC . 'company.inc.php';
				if (isset($company[$urlBase])) {

					if (is_array($company[$urlBase])) {
						if (isset($company[$urlBase]['LANGUAGE'])) {
							$lang = $company[$urlBase]['LANGUAGE'];
						}

						if (isset($company[$urlBase]['CONFIGFILE'])) {
							if (file_exists(ETC . $company[$urlBase]['CONFIGFILE'])) {
								require ETC . $company[$urlBase]['CONFIGFILE'];
							} else {
								header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
								//header('HTTP/1.0 500 Error company file');
								echo "Error company file";
								exit;
							}
						} else {
							header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
							//header('HTTP/1.0 500 Error company param');
							echo "Error company param";
							exit;
						}

						if (isset($company[$urlBase]['TIMEZONE'])) {
							date_default_timezone_set($company[$urlBase]['TIMEZONE']);
							ini_set('date.timezone', $company[$urlBase]['TIMEZONE']);
						}

						if (isset($company[$urlBase]['DEC_POINT'])) {
							$DEC_POINT = $company[$urlBase]['DEC_POINT'];
						}

						if (isset($company[$urlBase]['THOUSANDS_SEP'])) {
							$THOUSANDS_SEP = $company[$urlBase]['THOUSANDS_SEP'];
						}

					} else {
						if (file_exists(ETC . $company[$urlBase])) {
							require ETC . $company[$urlBase];
						} else {
							header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
							//header('HTTP/1.0 500 Error company param');
							echo "Error company file";
							exit;
						}
					}

				} else {
					header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
					//header('HTTP/1.0 500 Error company param');
					echo "Error company not found";
					exit;
				}

			} else {
				require ETC . 'config.inc.php';
			}

			if (!defined('DEC_POINT')) {
				if (isset($DEC_POINT)) {
					define('DEC_POINT', $DEC_POINT);
				} else {
					define('DEC_POINT', ',');
				}
			}

			if (!defined('THOUSANDS_SEP')) {
				if (isset($THOUSANDS_SEP)) {
					define('THOUSANDS_SEP', $THOUSANDS_SEP);
				} else {
					define('THOUSANDS_SEP', '.');
				}

			}

			$conn = $parametro->get("ConnectBD", true);

			$this->logs = new Logs($parametro);

			if ($conn) {
				$conexion = new Connect($parametro, $this->logs, $slave);
				$parametro = new Parametrosdb($parametro, $conexion);
				$this->conexion = $conexion;
			}
				
			if($server == true){
				$this->parametro = $parametro;
				$this->basic = new Basic($parametro, $conexion, $this->logs, false);
				$this->curl = new curl($parametro, $this->logs);
				$this->language = Language::getInstance();
			} else {	
				$this->parametro = $parametro;
	
				$this->plantilla = new plantilla($lang);
	
				$this->protect = new Protect($parametro, $conexion, $this->logs, $lang);
	
				$this->language = Language::getInstance();
	
				$this->basic = new Basic($parametro, $conexion, $this->logs, $this->language);
	
				$this->bmonitor = new bmonitor($parametro, $conexion, $this->logs, $this->language, $this->protect, $this->basic, $this->plantilla);
	
				$this->generate = new generate($parametro, $conexion, $this->logs, $this->language, $this->protect, $this->basic, $this->plantilla);
	
				$this->graph = new Graph($parametro, $conexion, $this->logs);
				$this->charts = new Charts($parametro, $conexion, $this->logs);
	
				$this->curl = new curl($parametro, $this->logs);
			}
		}

		private function isMaster() {
			$server = $this->parametro->get('BSWMASTER');

			$server_active = $this->parametro->get('BSW_SERVER');

			if ($server === $server_active) {
				return true;
			} else {
				return false;
			}
		}

		public function _crontab($crontab = false, $estado, $ignore = false, $diff = 8600) {
			if ($crontab) {
				
				$validCrontabSQL = "SELECT `id_crontab` FROM `bm_crontab` WHERE `type_crontab` = '$crontab'";
				
				$validCrontabRESULT = $this->conexion->queryFetch($validCrontabSQL);		
				
				if(!$validCrontabRESULT) {
					$insert = "INSERT IGNORE INTO `bm_crontab` ( `type_crontab`, `url_crontab`, `estado`, `ciclo`, 
					`exec_max_time`, `fecha_hora_inicio`, `fecha_hora_fin`, `host`, `is_master`)
					VALUES
					( '$crontab', 'serverFix.php', 'pendiente', 300, 600, NULL, NULL, NULL, 'true');";
					$this->conexion->query($insert);
				}

				$pid = getmypid();
				$ip = $this->logs->getIP();

				switch ($estado) {
					case 'start' :

						//validando activo
						// @formatter:off
						$valid_crontab_sql = "SELECT `estado`,`is_master`,`estado`, 
                                                TIME_TO_SEC(TIMEDIFF(NOW(), `fecha_hora_inicio`)) as DiffDay 
                                                    FROM `bm_crontab` WHERE `type_crontab`='$crontab';";
						// @formatter:on
						$valid_crontab = $this->conexion->queryFetch($valid_crontab_sql);

						if ($valid_crontab) {

							$isMaster = $this->isMaster();
							$isSlavePermit = $valid_crontab[0]['is_master'];
							$validestado = $valid_crontab[0]['estado'];
							$DiffDay = $valid_crontab[0]['DiffDay'];

							if ($isMaster || $isSlavePermit === 'false') {

								if ($validestado == 'pendiente' || ($DiffDay > $diff && $ignore)) {
									// @formatter:off
									$update_crontab_sql = "UPDATE `bm_crontab` SET `estado` = 'activo', 
                                                            `pid_crontab`='$pid' , `host`='$ip' , 
                                                            `fecha_hora_inicio` = NOW() , 
                                                            `fecha_hora_fin` = NULL 
                                                            WHERE `type_crontab` = '$crontab';";
									// @formatter:on
									$update_crontab = $this->conexion->query($update_crontab_sql);
									if ($update_crontab) {
										return true;
									} else {
										return false;
									}

								} else {
									return false;
								}
							} else {
								return false;
							}

						} else {
							return false;
						}

						break;

					case 'finish' :
						// @formatter:off
						$update_crontab_sql = "UPDATE `bm_crontab` SET `estado` = 'pendiente', 
                                                    `pid_crontab`='$pid' , `host`='$ip' , 
                                                    `fecha_hora_fin` = NOW() WHERE `type_crontab` = '$crontab';";
						// @formatter:on
						$update_crontab = $this->conexion->query($update_crontab_sql);
						if ($update_crontab) {
							return true;
						} else {
							return false;
						}
						break;
				}
			}
		}

	}

}
?>