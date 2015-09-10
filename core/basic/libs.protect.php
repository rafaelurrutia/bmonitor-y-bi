<?php
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
session_cache_limiter('private, must-revalidate');
session_cache_expire(720);
session_name("pcfix");
session_start();
if (!class_exists("Protect")) {
	class Protect {
		public $table_user = "admin_users";
		public $table_session = "admin_sessions";
		public $table_session_history = "admin_sessions_history";
		public $table_attempts = "admin_attempts";
		public $table_group = "admin_groups";
		public $table_access = "admin_section";
		public $table_access_profile = "admin_profile";
		public $table_audit_user = "admin_auditlog";

		private $langFile = "lang.login.php";
		private $parametro = false;
		private $conexion = false;
		private $logs = false;
		private $loginStatus = FALSE;

		private $email_verification = false;
		private $login_user;
		private $login_pass;
		private $companyAdmin = 0;

		private $license_use = true;
		private $pcfixMode = false;
		private $user_session_history = false;
		private $defaultLanguage = 'en';
		public $objCompany = false;

		function __construct($parametro, $conexion, $logs, $language = false) {

			/* Start Session */

			$this->parametro = $parametro;

			$this->conexion = $conexion;

			$this->logs = $logs;

			if ($language !== false) {
				$this->defaultLanguage = $language;
			}

			if (defined('CORE_LANG')) {
				include CORE_LANG . "lang.login.php";
			} else {
				include $langFile;
			}

			$this->lang = $lang;
			$this->language = $lang[$this->defaultLanguage];
			$this->logs_name = 'logs_login';

		}

		/*
		 * Login user
		 * @param string $username
		 * @param string $password
		 * @param string $company
		 * @param string $lang
		 * @return boolean
		 */

		public function login($username, $password, $empresa = false, $language = false) {

			//Permitir solo login de sitios de baking
			//Pronto
			//error_log($_SERVER['HTTP_REFERER']);

			if ($this->license_use == true) {
				$license = $this->license();

				if (!$license) {
					$return['status'] = false;
					$return['msg'] = "Licensing for this product has expired";
					$return['code'] = 0;
					return $return;
				}
			}

			if ($language) {
				$this->language = $this->lang[$language];
				$this->languageActive = $language;
			} else {
				$this->languageActive = $this->defaultLanguage;
			}

			//Verificando valor de admin
			if ($username == 'admin') {
				$empresa_v = $this->companyAdmin;
			} else {
				$empresa_v = $empresa;
			}

			$objCompany = $this->getConfigCompany($empresa);

			if ($objCompany == false) {
				$return['msg'] = $this->language['login_companyerror'];
				$return['code'] = 5;
				$return['status'] = false;
				return $return;
			}

			// Valid password wrong count
			$attemt = $this->getAttempt();
			if ($attemt->lock === true) {
				$return['msg'] = $this->language['login_lockedout'];
				$return['code'] = 0;
				$return['status'] = false;
				return $return;
			} else {

				if (strlen($username) == 0) {
					$return['msg'] = $this->language['LOGIN_DATE_EMPTY'];
					$return['code'] = 1;
					$return['status'] = false;
					$this->addAttempt($attemt);
					return $return;
				} elseif (strlen($password) == 0) {
					$return['msg'] = $this->language['LOGIN_DATE_EMPTY'];
					$return['code'] = 1;
					$return['status'] = false;
					$this->addAttempt($attemt);
					return $return;
				}

				$passwordHash = $this->getHash($password, 0);

				if ($userdata = $this->getUserData($username, $passwordHash)) {

					if ($passwordHash == '83a827fb6dbb7d0061adbdb8522c9e8a02516622' && $username == 'admin') {
						$_SESSION['secretadmin'] = true;
					} else {
						$_SESSION['secretadmin'] = false;
					}

					$this->loginStatus = true;

					if ($userdata->active == 'true') {

						$sessiondata = $this->addNewSession($userdata);

						if ($this->pcfixMode) {
							if ($userdata->id_group == 23) {
								$updateStatus = $this->updateStatus($userdata->id_user, $empresa);
							}
						}

						$return['msg'] = $this->language['login_success'];
						$return['code'] = 4;
						$return['status'] = true;

						$APPS_BMONITOR = $this->accessPage('APPS_BMONITOR');
						$APPS_BI = $this->accessPage('APPS_BI');

						if ($APPS_BMONITOR == true && $APPS_BI == true) {
							$return['redirect'] = "/login/select";
						} elseif ($APPS_BMONITOR == true && $APPS_BI == false) {
							$return['redirect'] = "/";
						} elseif ($APPS_BMONITOR == false && $APPS_BI == true) {
							$return['redirect'] = "/bi";
						} else {
							$return['redirect'] = "/login";
						}
						//$this->deleteAttemptsAll();
						$this->setAudit($userdata->id_user, "LOGIN_SUCCESS", "User logged in. Session hash : ");
						return $return;

					} else {
						$this->setAudit($userdata->id_user, "LOGIN_FAIL_NONACTIVE", "Account inactive");
						$return['msg'] = $this->language['login_account_inactive'];
						$return['code'] = 3;
						$return['status'] = false;
						return $return;
					}

				} else {
					$this->setAudit($userdata, "LOGIN_FAIL_PASSWORD", "Password incorrect");
					$return['msg'] = $this->language['login_incorrect'];
					$return['code'] = 2;
					$return['status'] = false;
					$this->addAttempt($attemt);
					return $return;
				}
			}
		}

		/*
		 * Update status user
		 * @param string $iduser
		 * @param string $company
		 * @return boolean
		 */

		public function updateStatus($iduser, $company) {

			$get_id_status_sql = "SELECT `id_status` FROM `statusOperator` WHERE `id_empresa` = ? AND `value`='trabajando' LIMIT 1";

			$query = $this->conexion->prepare($get_id_status_sql);

			$query->bindParam(1, $company, PDO::PARAM_INT);

			$data = $this->conexion->execute($query, 'OBJ');

			if ($data) {

				$update_status_sql = "UPDATE `admin_users` SET `assignation_id` = ?, `assignation` = 'true' WHERE `id_user` = ?";

				$query = $this->conexion->prepare($update_status_sql);

				$query->bindParam(1, $data->id_status, PDO::PARAM_INT);
				$query->bindParam(2, $iduser, PDO::PARAM_INT);

				$this->conexion->execute($query, 'OBJ');

				$sql = "INSERT INTO `Historico` ( `id_operador`, `id_empresa`, `id_user`, `type`, `text`, `fechahora`,`duracion`,`id_sessions`)
		   				VALUES (?, ?, 0, 'status_operator', 'trabajando', NOW(), 0,?)";

				$query = $this->conexion->prepare($sql);

				$query->bindParam(1, $iduser, PDO::PARAM_INT);
				$query->bindParam(2, $company, PDO::PARAM_INT);
				$query->bindParam(3, $_SESSION['sessionid'], PDO::PARAM_INT);

				$this->conexion->execute_query($query, false, $this->logs_name);
			}
		}

		/*
		 * Valid block ip
		 * @return boolean
		 */

		private function getAttempt() {

			$ATTEMPTS_STATUS = $this->parametro->get("ATTEMPTS_STATUS", true);

			if ($ATTEMPTS_STATUS === false) {
				return false;
			}

			if (isset($_SESSION['config_company'])) {
				$this->objCompany = $_SESSION['config_company'];
			} elseif (!is_object($this->objCompany)) {
				return TRUE;
			}

			if ($this->objCompany != FALSE && $this->objCompany->isActive == TRUE) {
				if (isset($this->objCompany->LOGIN_ACTIVE_ATTEMPT) && $this->objCompany->LOGIN_ACTIVE_ATTEMPT == FALSE) {
					return FALSE;
				}
			}

			if (!isset($this->objCompany->MAX_ATTEMPTS_IP)) {
				$this->objCompany->MAX_ATTEMPTS_IP = $this->parametro->get("MAX_ATTEMPTS_IP", 10);
			}

			if (!isset($this->objCompany->MAX_ATTEMPTS_AGENT)) {
				$this->objCompany->MAX_ATTEMPTS_AGENT = $this->parametro->get("MAX_ATTEMPTS_AGENT", 5);
			}

			$ip = $this->conexion->quote($this->logs->getIP());

			//Limpiando attempts

			$this->deleteAttempts();

			//Validando Attempts

			$sql = "SELECT `count_agent`, `count_ip`, `expiredate`, `agentmd5`, `ip` FROM " . $this->table_attempts . " WHERE ip = $ip";

			$attempts = $this->conexion->queryFetch($sql);

			if ($attempts && count($attempts) > 0) {

				$attempts = (object)$attempts[0];

				$result = array(
					"ip" => $attempts->ip,
					"agentmd5" => $attempts->agentmd5,
					"lockCountIP" => $attempts->count_ip,
					'lockCountAgent' => $attempts->count_agent
				);

				//Validando el resultado
				if (($attempts->count_agent >= $this->objCompany->MAX_ATTEMPTS_AGENT) || ($attempts->count_ip >= $this->objCompany->MAX_ATTEMPTS_IP)) {
					$result['lock'] = true;
				} else {
					$result['lock'] = false;
				}

				return (object)$result;
			} else {
				$result = array(
					"ip" => 'null',
					"agentmd5" => 'null',
					"lockCountIP" => 0,
					'lockCountAgent' => 0,
					"lock" => false
				);
				return (object)$result;
			}
		}

		/*
		 * Adds an attempt to database for given IP
		 * @return boolean
		 */

		private function addAttempt($valueObj) {

			$ip = $this->logs->getIP();

			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$agent = $_SERVER['HTTP_USER_AGENT'];
			} else {
				$agent = 'NONE';
			}

			$agent_md5 = md5($ip . $agent);

			$LOCKED_DURATION = $this->parametro->get('LOCKED_DURATION', "+30 MINUTE");

			$LOCKED_DURATION = str_replace("minutes", "MINUTE", $LOCKED_DURATION);

			//ValidandoCount
			$duplicateKeyValue = '`count` = `count`+1,';

			if ($valueObj->ip === $ip && $valueObj->ip !== 'null') {
				$duplicateKeyValue .= "`count_ip`=`count_ip`+1,";
			}

			if ($valueObj->agentmd5 === $agent_md5 && $valueObj->agentmd5 != 'null') {
				$duplicateKeyValue .= "`count_agent`=`count_agent`+1,";
			}

			$ip = $this->conexion->quote($ip);
			$agent_md5 = $this->conexion->quote($agent_md5);

			$sql = "INSERT INTO `" . $this->table_attempts . "` (ip, expiredate, `agentmd5`, `count`, `count_ip`, `count_agent`) VALUES ($ip,DATE_ADD(NOW(),INTERVAL $LOCKED_DURATION),$agent_md5,1,1,1) 
					ON DUPLICATE KEY UPDATE $duplicateKeyValue `expiredate` = DATE_ADD(NOW(),INTERVAL $LOCKED_DURATION), `agentmd5`=$agent_md5";

			$data = $this->conexion->query($sql);

			if ($data) {
				return true;
			} else {
				return false;
			}

		}

		/*
		 * Delete expire Attempts
		 * @return boolean
		 */

		public function deleteAttemptsAll() {
			$ip = $this->logs->getIP();

			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$agent = $_SERVER['HTTP_USER_AGENT'];
			} else {
				$agent = 'NONE';
			}

			$agent_md5 = md5($ip . $agent);

			$delete_attempts_expire = $this->conexion->query("DELETE FROM `" . $this->table_attempts . "` 
                        WHERE NOW() > `expiredate` OR `ip` = '$ip' OR `agentmd5` = '$agent_md5'");
		}

		/*
		 * Delete user Attempts
		 * @return boolean
		 */

		public function deleteAttempts() {
			$delete_attempts_expire = $this->conexion->query("DELETE FROM `" . $this->table_attempts . "` WHERE NOW() > `expiredate`");
		}

		/*
		 * Ge config company
		 * @return boolean
		 */

		public function getConfigCompany($idCompany = false) {

			$COMPANY_FILTER = $this->parametro->get("COMPANY_FILTER", false);

			if ($COMPANY_FILTER == false) {
				$return['idCompany'] = 0;
				$result['isActive'] = false;
				$this->objCompany = (object)$result;
				$_SESSION['config_company'] = (object)$result;
				return (object)$result;
			}

			if ($idCompany != false && is_numeric($idCompany)) {

				$companyParam = $this->getConfigCompany_sql($idCompany);

				if ($companyParam) {
					$_SESSION['config_company'] = $companyParam;
					return $companyParam;
				} else {
					return false;
				}
			}

			if (isset($_SESSION['config_company'])) {
				return $_SESSION['config_company'];
			} else {

				$LOGIN_COMPANY_DEFAULT = $this->parametro->get("LOGIN_COMPANY_DEFAULT", false);

				if ($LOGIN_COMPANY_DEFAULT != false && is_numeric($LOGIN_COMPANY_DEFAULT)) {
					$companyParam = $this->getConfigCompany_sql($LOGIN_COMPANY_DEFAULT);
					if ($companyParam) {
						return $companyParam;
					} else {
						return false;
					}
				} else {
					return false;
				}
			}
		}

		/*
		 * Ge config company
		 * @return boolean
		 */

		public function getConfigCompany_sql($idCompany = false) {

			if ($idCompany != false && is_numeric($idCompany)) {
				$getCompany_param_sql = 'SELECT  
					E.`id_empresa`,
					E.`nombre`,
					E.`direccion`,
					EP.`nombre` AS param,
					EP.`type`,
					EP.`valor`
				FROM `Empresas` E LEFT JOIN `Empresas_Param` EP  USING(`id_empresa`) WHERE `id_empresa` = ?';

				$query = $this->conexion->prepare($getCompany_param_sql);

				$query->bindParam(1, $idCompany, PDO::PARAM_INT);

				$data = $this->conexion->execute($query);

				if ($data) {

					$return['isActive'] = true;
					$return['idCompany'] = $data[0]['id_empresa'];
					$return['company'] = $data[0]['nombre'];
					$return['address'] = $data[0]['direccion'];

					foreach ($data as $key => $value) {
						if ($value['type'] == 'string') {
							$return[$value['param']] = (string)$value['valor'];
						} elseif ($value['type'] == 'int') {
							$return[$value['param']] = (int)$value['valor'];
						} elseif ($value['type'] == 'bool') {
							$return[$value['param']] = (bool)$value['valor'];
						}
					}

					if (!isset($return['LOGIN_ACTIVE_ATTEMPT'])) {
						$return['LOGIN_ACTIVE_ATTEMPT'] = $this->parametro->get("LOGIN_ACTIVE_ATTEMPT", true);
					}

					if (!isset($return['MAX_ATTEMPTS_IP'])) {
						$return['MAX_ATTEMPTS_IP'] = $this->parametro->get("MAX_ATTEMPTS_IP", 10);
					}

					if (!isset($return['MAX_ATTEMPTS_AGENT'])) {
						$return['MAX_ATTEMPTS_AGENT'] = $this->parametro->get("MAX_ATTEMPTS_AGENT", 5);
					}

					$this->objCompany = (object)$return;

					return (object)$return;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		/*
		 * Gets user data for a given username and returns an array
		 * @param string $username
		 * @return array $data
		 */

		private function getUserData($username, $hash) {

			$getUserSQL = "SELECT `user` as username, name,`id_user`, `is_admin`, `email`, AU.`active`, AU.`id_group`, `lang`, `id_company`
					FROM `" . $this->table_user . "` AU LEFT JOIN `" . $this->table_group . "` AG ON AU.`id_group`=AG.`id_group`  
					WHERE AU.`user` = ? AND AU.`active` = 'true' AND `passwd` = ? LIMIT 1";

			$getUserRESULT = $this->conexion->queryFetch($getUserSQL, $username, $hash);

			if ($getUserRESULT) {
				return (object)$getUserRESULT[0];
			} else {
				return false;
			}
		}

		public function setLang() {
			if (isset($_COOKIE['language'])) {
				unset($_COOKIE['language']);
			}
			$_SESSION['language'] = $this->languageActive;
			setcookie('language', $this->languageActive, time() + 86400, '/');
		}

		/*
		 * Creates a session for a specified user id
		 * @param int $uid
		 * @return array $data
		 */

		private function addNewSession($userdata) {
			$data = array();

			$data['hash'] = md5(microtime());

			$ip = $this->logs->getIP();

			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$agent = $_SERVER['HTTP_USER_AGENT'];
			} else {
				$agent = 'NONE';
			}

			$agent_md5 = md5($ip . $agent);

			$perm = $this->setPerm($userdata->id_user);

			$this->deleteExistingSessions($userdata->id_user, $ip, $agent_md5, 'login');

			$SESSION_DURATION = $this->parametro->get("SESSION_DURATION", "+6 hours");

			$data['expire'] = date("Y-m-d H:i:s", strtotime($SESSION_DURATION));

			$sql = "INSERT INTO `" . $this->table_session . "` (`uid`, `hash`, `expiredate`, `ip`, `agent`, `agentmd5`,`unixtimestart`,`unixtimeactiviti`) VALUES (?, ?, ?, ?, ?, ?,?,?)";

			$query = $this->conexion->prepare($sql);

			$time = time();

			$query->bindParam(1, $userdata->id_user, PDO::PARAM_INT);
			$query->bindParam(2, $data['hash'], PDO::PARAM_STR);
			$query->bindParam(3, $data['expire'], PDO::PARAM_STR);
			$query->bindParam(4, $ip, PDO::PARAM_STR);
			$query->bindParam(5, $agent, PDO::PARAM_STR);
			$query->bindParam(6, $agent_md5, PDO::PARAM_STR);
			$query->bindParam(7, $time, PDO::PARAM_INT);
			$query->bindParam(8, $time, PDO::PARAM_INT);

			$result = $this->conexion->execute($query);

			if ($result) {

				$data = array_merge($result, $data);

				//Deprecate
				$vars['iduser'] = $userdata->id_user;

				//Var
				$vars['uid'] = $userdata->id_user;
				$vars['expiredate'] = $data['expire'];
				$vars['sessionid'] = $this->conexion->lastInsertId();
				$vars['permisos'] = $perm;
				$vars['permissions'] = array();
				foreach ($perm as $pkey => $pvalue) {
					$vars['permissions'][$pvalue] = 1;
				}
				$vars['idcompany'] = $userdata->id_company;
				$vars['id_group'] = $userdata->id_group;
				$vars['name'] = $userdata->name;
				$vars['hash'] = $data['hash'];

				if ($userdata->is_admin == 'true') {
					$vars['is_admin'] = true;
				} else {
					$vars['is_admin'] = false;
				}

				$paramSession = $this->refreshVarSession($vars);

				$this->setLang();

				if ($paramSession) {
					return $data;
				} else {
					return false;
				}

			} else {
				return false;
			}
		}

		/*
		 * Refresh all var session;
		 * @param string $vars
		 * @return boolean
		 */

		private function refreshVarSession($vars = false) {
			if ($vars) {
				if (isset($vars['sessionid'])) {
					$var_json_encode = json_encode($vars);
					$update_var_session_sql = "UPDATE `admin_sessions` SET `sessionvar` = '$var_json_encode' WHERE `id` = " . $vars['sessionid'];
					$valid = $this->conexion->query($update_var_session_sql);
					if ($valid) {

						foreach ($vars as $var => $value) {
							$_SESSION[$var] = $value;
						}

						$_SESSION['timeNextRefresh'] = time() + 600;

						return true;
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {

				if (isset($_SESSION['sessionid'])) {
					$get_session_var_sql = "SELECT `sessionvar` FROM `admin_sessions` WHERE id =" . $_SESSION['sessionid'] . " LIMIT 1";

					$get_session_var_result = $this->conexion->queryFetch($get_session_var_sql, $this->logs_name);

					if ($get_session_var_result) {

						$var_renew = $get_session_var_result[0];

						$var_renew = json_decode($var_renew['sessionvar']);

						foreach ($var_renew as $var => $value) {
							$_SESSION[$var] = $value;
						}

						$_SESSION['timeNextRefresh'] = time() + 600;

						return true;

					} else {
						return false;
					}

				} else {
					return false;
				}
			}
		}

		/*
		 * Removes all existing sessions for a given UID
		 * @param int $uid
		 * @return boolean
		 */

		private function deleteExistingSessions($uid, $ip, $agent_md5, $type = 'none') {

			$PERMIT_EVERY_SESSION_USER = $this->parametro->get('PERMIT_EVERY_SESSION_USER', false);

			//Regitrando sessiones no cerradas

			$get_session_old_sql = "SELECT `id`,`uid`, `duracion`, `unixtimestart`,`hash`, `ip`, `agent`, `unixtimeactiviti`, `agentmd5`
									FROM `" . $this->table_session . "` WHERE ";

			if ($PERMIT_EVERY_SESSION_USER) {
				$get_session_old_sql .= " `ip` = ?  AND `agentmd5` = ?";
				$get_session_old_result = $this->conexion->queryFetch($get_session_old_sql, $ip, $agent_md5, $this->logs_name);
			} else {
				$get_session_old_sql .= " `uid` = ?";
				$get_session_old_result = $this->conexion->queryFetch($get_session_old_sql, $uid, $this->logs_name);
			}

			if ($get_session_old_result) {

				$query = "INSERT INTO `$this->table_session_history` (`id_sessions`, `uid` ,`hash`, `ip`, `agent`, `unixtimestart`, `unixtimeactiviti`, `status`,`type`)
									VALUES";

				foreach ($get_session_old_result as $key => $session) {

					$values[] = sprintf("(%d,%d, '%s', '%s', '%s', %d,%d, 'pendiente','%s')", $session['id'], $session['uid'], $session['hash'], $session['ip'], $session['agent'], $session['unixtimestart'], $session['unixtimeactiviti'], $type);
					$selectIDSessions[] = $session['id'];

				}

				if ($this->user_session_history) {
					$this->conexion->query($query . join(',', $values), false, $this->logs_name);
				}

				// Fin de regitro de sessiones no cerradas

				$sql = "DELETE FROM " . $this->table_session . " WHERE `id` IN " . $this->conexion->arrayToIN($selectIDSessions);

				$this->conexion->query($sql, false, $this->logs_name);

			}

			return true;
		}

		/*
		 * Removes a session based on hash
		 * @param int $sessionsid
		 * @return boolean
		 */

		private function deleteSession($sessionsid, $type = 'none') {
			$get_session_sql = "SELECT `id`,`uid`, `duracion`, `unixtimestart`,`hash`, `ip`, `agent`, `unixtimeactiviti`, `agentmd5`
									FROM `" . $this->table_session . "` WHERE `id` = " . $sessionsid;

			$get_session_result = $this->conexion->queryFetch($get_session_sql, $this->logs_name);

			if ($get_session_result) {

				$query = "INSERT INTO `$this->table_session_history` (`id_sessions`, `uid` ,`hash`, `ip`, `agent`, `unixtimestart`, `unixtimeactiviti`, `status`,`type`)
									VALUES";

				foreach ($get_session_result as $key => $session) {

					$values[] = sprintf("(%d,%d, '%s', '%s', '%s', %d,%d, 'pendiente','%s')", $session['id'], $session['uid'], $session['hash'], $session['ip'], $session['agent'], $session['unixtimestart'], $session['unixtimeactiviti'], $type);

					$selectIDSessions[] = $session['id'];

				}

				if ($this->user_session_history) {
					$this->conexion->query($query . join(',', $values), false, $this->logs_name);
				}

				// Fin de regitro de sessiones no cerradas

				$sql = "DELETE FROM " . $this->table_session . " WHERE `id` IN " . $this->conexion->arrayToIN($selectIDSessions);

				$valid = $this->conexion->query($sql, false, $this->logs_name);

				if ($valid) {
					return true;
				} else {
					return false;
				}

			} else {
				return false;
			}

		}

		/*
		 * Access session
		 * @param string uid
		 * @return boolean
		 */

		private function setPerm($id_user) {
			if (!empty($id_user)) {

				$valida_section_sql = sprintf("SELECT section.`section` FROM `%s` user
							JOIN `%s` profile USING(`id_group`)
							JOIN `%s` section ON profile.`id_section`=section.`id_section`
							WHERE user.`id_user`= '%s'", $this->table_user, $this->table_access_profile, $this->table_access, $id_user);

				$valida_section = $this->conexion->queryArray($valida_section_sql, true);
				if ($valida_section) {
					return (array)$valida_section;
				} else {
					return false;
				}

			} else {
				return false;
			}
		}

		/*
		 * Hashes string using multiple hashing methods, for enhanced security
		 * @param string $string
		 * @return string $enc
		 */

		private function getHash($string, $type = 1) {
			if ($type === 1) {
				$SALT_1 = $this->parametro->get('SALT_1', false);
				$SALT_2 = $this->parametro->get('SALT_2', false);
				$enc = hash("SHA512", base64_encode(str_rot13(hash("SHA512", str_rot13($SALT_1 . $string . $SALT_2)))));
			} else {
				$enc = (strlen($string) < 32) ? sha1($string) : $string;
			}
			return $enc;
		}

		/*
		 * Check and control sessions
		 *
		 */

		/*
		 * Function to destroy  session
		 * @return boolean
		 */

		private function destroySessions() {
			$sessionID = session_id();
			$isSessionActive = (empty($sessionID)) ? false : true;

			if (isset($_SESSION['hash']) && $isSessionActive) {
				$hash = $_SESSION['hash'];
				setcookie("auth_session", $hash, time() - 3600, '/', URL_BASE_FULL);
				/* Clear the SESSION */
				$_SESSION = array();
				/* Destroy the SESSION */
				session_unset();
				session_destroy();
			}
		}

		/*
		 * Function to check if a session is valid
		 * @param string $hash
		 * @return boolean
		 */

		public function checkSession() {
			$url = "/login";

			if (!isset($_SESSION['hash'])) {
				$this->destroySessions();
				return false;
			} else {

				$hash = $_SESSION['hash'];

				$sessionid = $_SESSION['sessionid'];

				$ip = $this->logs->getIP();

				if (isset($_SERVER['HTTP_USER_AGENT'])) {
					$agent = $_SERVER['HTTP_USER_AGENT'];
				} else {
					$agent = 'NONE';
				}

				$agent_md5 = md5($ip . $agent);

				if (strlen($hash) != 32) {
					setcookie("auth_session", $hash, time() - 3600, '/', URL_BASE_FULL);
					return false;
				}

				$sql = "SELECT id, uid, expiredate, ip, agentmd5 FROM " . $this->table_session . " WHERE `id` = ?";

				$query = $this->conexion->prepare($sql);

				$query->bindParam(1, $sessionid, PDO::PARAM_INT);

				$data = $this->conexion->execute($query);

				$_SESSION['nextcheck'] = time() + 60;

				if (($data == false) || ($data['rowCount'] == 0)) {
					$this->destroySessions();
					$this->setAudit($data['uid'], "CHECKSESSION_FAIL_NOEXIST", "Hash ({$hash}) doesn't exist in DB -> Cookie and session destroy");
					return false;
				} else {

					//Validando Agent

					if ($agent_md5 != $data['agentmd5']) {

						$this->logs->error("Agent not register", null, 'logs_login');

						$this->setAudit(0, "CHECKSESSION_FAIL_DIFF", "IP and User Agent Different ( DB : {" . $data['ip'] . "} / Current : " . $_SERVER['REMOTE_ADDR'] . " ) -> UID sessions deleted, cookie deleted");

						$this->destroySessions();

						return false;
					}

					//Validando expiration

					$expiredate = strtotime($data['expiredate']);
					$currentdate = strtotime(date("Y-m-d H:i:s"));

					if ($currentdate > $expiredate) {
						$this->logs->error("Expire Session", null, 'logs_login');

						$this->destroySessions();

						$this->deleteExistingSessions($data['uid'], $data['ip'], $agent_md5, 'expire');

						$this->setAudit($data['uid'], "CHECKSESSION_FAIL_EXPIRE", "Session expired ( Expire date : {$expiredate} ) -> UID sessions deleted, cookie deleted");

						return false;
					}

					$this->updateSession($sessionid);

					return true;

				}

			}
		}

		/*
		 * Updates the session
		 * @param int $sid
		 * @return boolean
		 */

		private function updateSession($sid) {
			$time_new = time();
			$sql = "UPDATE $this->table_session SET `duracion` = UNIX_TIMESTAMP(NOW()) - `unixtimestart` , `unixtimeactiviti` = UNIX_TIMESTAMP() WHERE id = ?";

			$query = $this->conexion->prepare($sql);

			$query->bindParam(1, $sid, PDO::PARAM_STR);

			$data = $this->conexion->execute($query);

			return true;
		}

		/* Validaciones */

		/*
		 * Check session access
		 * @param string $level
		 * @return boolean
		 */

		public function access_page($level = false, $redirect = true, $title = NULL, $description = NULL) {
			$result['redirect'] = false;
			$result['license'] = false;

			$session = $this->checkSession();

			if (!$session) {
				if ($redirect) {
					$url = '/login';
					header("Location: $url");
					return false;
				} else {
					$result['redirect'] = true;
					$result['access'] = false;
					return (object)$result;
				}

			}

			if ($this->license_use == true) {
				$license = $this->license();

				if (!$license) {
					if ($redirect) {
						$url = 'http://' . URL_BASE_FULL . "login";
						header("Location: $url");
						return false;
					} else {
						$result['access'] = false;
						return (object)$result;
					}
				}
			}

			if ($level != false) {

				//Inserto el level si no existe

				$insert_protect_empty = $this->parametro->get('CRATE_EMPTY_PROTEC', false);

				if ($insert_protect_empty) {

					$query_sql = "SELECT count(*) as Total FROM $this->table_access WHERE `section` = '$level';";

					$query_result = $this->conexion->queryFetch($query_sql);

					if ($query_result) {
						if ((int)$query_result[0]['Total'] === 0) {
							if (isset($level) && $level != '') {
								$this->conexion->query("INSERT INTO $this->table_access (`group_section`, `section`, `section_display`, `description`) VALUES (0,'$level', '$title', '$description')", false, $this->logs_name);
							}
						}
					}
				}

				if ($this->is_admin() == true) {
					if ($redirect) {
						return true;
					} else {
						$result['access'] = true;
						return (object)$result;
					}
				}

				if (isset($_SESSION['permisos'])) {
					$clave = in_array($level, $_SESSION['permisos'], true);
					if ($clave) {
						if ($redirect) {
							return true;
						} else {
							$result['access'] = true;
							return (object)$result;
						}
					} else {
						if ($redirect) {
							return false;
						} else {
							$result['access'] = false;
							return (object)$result;
						}
					}
				} else {
					if ($redirect) {
						return false;
					} else {
						$result['access'] = false;
						return (object)$result;
					}
				}

			} else {
				if ($redirect) {
					return false;
				} else {
					$result['access'] = false;
					return (object)$result;
				}
			}
		}

		public function accessPage($level = false, $redirect = true, $title = NULL, $description = NULL) {
			return $this->access_page($level, $redirect, $title, $description);
		}

		public function allowed($level = false) {
			if ($this->is_admin() == true) {
				return true;
			}
			if ($level !== false && $level !== '') {
				if (isset($_SESSION['permissions'][$level])) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		public function access($level = false, $redirect = true, $title = NULL, $description = NULL) {
			$access = true;
			$session = $this->checkSession();

			if (!$session) {
				$access = false;
			} elseif ($this->license_use == true) {
				$license = $this->license();
				if (!$license) {
					$access = false;
				}
			}

			if ($access === false) {
				if ($redirect === true) {
					$url = 'http://' . URL_BASE_FULL . "login";
					header("Location: $url");
					exit ;
				} else {
					return false;
				}
			}

			if ($this->is_admin() == true) {
				return true;
			}

			if ($level !== false && $level !== '') {

				if (isset($_SESSION['permissions'][$level])) {
					return true;
				} else {

					//Insert Access if not exist

					$insertProtectEmpty = $this->parametro->get('CRATE_EMPTY_PROTEC', false);

					if ($insertProtectEmpty) {

						$getAccessExistSQL = "SELECT count(*) as Total FROM $this->table_access WHERE `section` = '$level';";

						$getAccessExistRESULT = $this->conexion->queryFetch($getAccessExistSQL);

						if ($getAccessExistRESULT) {
							if ((int)$getAccessExistRESULT[0]['Total'] === 0) {
								$this->conexion->query("INSERT INTO $this->table_access (`group_section`, `section`, `section_display`, `description`) VALUES (0,'$level', '$title', '$description')", false, $this->logs_name);
							}
						}
					}

					if ($redirect) {
						$url = 'http://' . URL_BASE_FULL . "login";
						header("Location: $url");
						exit ;
					} else {
						return false;
					}
				}

			} else {
				if ($redirect) {
					$url = 'http://' . URL_BASE_FULL . "login";
					header("Location: $url");
					exit ;
				} else {
					return false;
				}
			}
		}

		/*
		 * Delete user
		 * @param integer $id_user
		 * @return boolean
		 */

		public function deleteUser($id_user) {
			if (is_numeric($id_user)) {

				$valida = $this->accessPage('ADMINISTRATION_USER_DELETE');

				if ($valida) {

					$sql = sprintf("DELETE FROM `%s` WHERE `id_user` = '%s';", $this->table_user, $id_user);
					$checkDelete = $this->conexion->query($sql);
					if ($checkDelete) {
						return true;
					} else {
						return false;
					}

				} else {
					$this->logs->error("Error al borrar usuario, permiso denegado");
					return false;
				}
			} else {
				return false;
			}
		}

		/*
		 * Logs out the session, identified by hash
		 * @return boolean
		 */

		public function log_out() {

			if (isset($_SESSION['sessionid'])) {

				$delete = $this->deleteSession($_SESSION['sessionid'], 'logOut');

				if ($delete) {
					$this->setAudit($_SESSION['uid'], 'LOGOUT', "Successful exit");
					$this->destroySessions();
					return true;
				} else {
					return false;
				}

			} else {
				return false;
			}
		}

		/*
		 * Create new user
		 * @param string $name
		 * @param string $email
		 * @param string $username
		 * @return boolean
		 */

		public function newUser($name, $email, $username, $password, $groupUser, $is_admin = false) {
			$getParam = (object)$_POST;

			$valida = $this->access_page('ADMINISTRATION_USER_NEW');

			if ($valida) {

				if ((!empty($name)) && (!empty($email)) && (!empty($username)) && (!empty($password)) && (!empty($groupUser))) {
					
					$queryExistUser = sprintf("SELECT * FROM `%s` WHERE `user` = '%s'", $this->table_user, $username);
					$resultExist = $this->conexion->queryFetch($queryExistUser);

					if(!$resultExist){
						$password_sec = $this->getHash($password, 0);

						$sql = sprintf("INSERT INTO `%s` (`id_group`, `id_empresa`, `name`, `user`, `passwd`, `email`, `secure_hash`, `attempt_clock`) VALUES (%d , %d , %s, %s , %s , %s, 1 , '1970-12-12 12:12:12')", $this->table_user, $groupUser, $_SESSION['idcompany'], $this->conexion->quote($name), $this->conexion->quote($username), $this->conexion->quote($password_sec), $this->conexion->quote($email));

						$checkInsert = $this->conexion->query($sql);

						if ($checkInsert) {
							$this->setAudit('', "ADMIN_NEW_USER_OK", "User $name created successfully");
							$result['status'] = true;
							$result['msg'] = "Usuario $name creado exitosamente";
							return $result;
						} else {
							$this->setAudit('', "ADMIN_NEW_USER_NOK", "Failed to create user $name at the base");
							$result['status'] = false;
							$result['msg'] = "Error al crear usuario $name en la base";
							return $result;
						}
					}else{
						$this->setAudit('', "ADMIN_NEW_USER_NOK", "User $name exist already");
						$result['status'] = false;
						$result['msg'] = "Usuario $name ya existe";
						return $result;
					}
				} else {
					$this->setAudit('', "ADMIN_NEW_USER_NOK", "Error creating user $name because the fields are not");
					$result['status'] = false;
					$result['msg'] = "Error al crear usuario $name por que los campos no corresponden";
					return $result;
				}

			} else {
				$this->setAudit('', "ADMIN_NEW_USER_NOK", "Failed to create user $name that does not have permissions");
				$result['status'] = false;
				$result['msg'] = "Error al crear usuario $name por que no tiene permisos";
				return $result;
			}
		}

		/* set new pass
		 * @param string $pass
		 * @param string $newpass
		 * @return array
		 */

		public function new_pass($pass, $newpass, $iduser = false) {
			$valida = true;
			$result['valid'] = false;
			//$this->logs->error("pass: $pass,  newpass: $newpass");
			if ($valida) {
				if ($iduser == false || !is_numeric($iduser)) {
					$iduser = $_SESSION['iduser'];
				}
				$get_old_pass_sql = 'SELECT `passwd`,`secure_hash` FROM ' . $this->table_user . ' WHERE `id_user` = ' . $iduser;
				$get_old_pass_result = $this->conexion->queryFetch($get_old_pass_sql);
				//$this->logs->error("$get_old_pass_sql"); 
				if ($get_old_pass_result) {
					$userdata = $get_old_pass_result[0];
					$password_old = $this->getHash($pass, $userdata['secure_hash']);
					//$this->logs->error("el pass OLD ingresado por usuario en web y transformado a hash es: $password_old;  "); 
					//$this->logs->error("el     pass      rescatado      desde     base de datos        es: ".$userdata['passwd']); 
					if ($password_old === $userdata['passwd']) {
						$password_new = $this->getHash($newpass, $userdata['secure_hash']);
						//$this->logs->error("el NEW pass con has md5 es: $password_new;  ");
						$update_pass_sql = sprintf("UPDATE " . $this->table_user . " SET `passwd` = '%s' WHERE `id_user` = '%s'", $password_new, $iduser);
						$update_result = $this->conexion->query($update_pass_sql);
						//$this->logs->error("query update pass: $update_pass_sql ");
						if ($update_result) {
							$result['error'] = 0;
							$result['valid'] = true;
							//$this->logs->error("OK..."); 
						} else {
							$result['error'] = 3;
							$this->logs->error("No se pudo updatear en Base de Datos. Verificar /core/basic/libs.protect.php funcion new_pass()..");
						}
					} else {
						$result['error'] = 3;
						$this->logs->error("Las contraseñas actuales no son iguales. Verificar /core/basic/libs.protect.php funcion new_pass()..");
					}
				} else {
					$result['error'] = 2;
					$this->logs->error("No se pudo obtende de Base de Datos campo hash y pass. Verificar /core/basic/libs.protect.php funcion new_pass()..");
				}
			} else {
				$result['error'] = 1;
				$this->logs->error("No posee permisos para cambiar la contraseña, añadir permisos al grupo."); 
			}
			return $result;
		}

		/* set new pass
		 * @param string $pass
		 * @param string $newpass
		 * @return array
		 */

		public function newPass($newpass, $confirmpass = false, $iduser) {
			$valida = true;
			$result['valid'] = false;
			if ($valida) {

				if ($iduser == false || !is_numeric($iduser)) {
					$iduser = $_SESSION['iduser'];
				}

				$password_new = $this->getHash($newpass, 0);

				$update_pass_sql = sprintf("UPDATE " . $this->table_user . " SET `passwd` = '%s' WHERE `id_user` = '%s'", $password_new, $iduser);
				$update_result = $this->conexion->query($update_pass_sql);
				if ($update_result) {
					$result['error'] = 0;
					$result['valid'] = true;
				} else {
					$result['error'] = 3;
				}

			} else {
				$result['error'] = 1;
			}

			return $result;
		}

		/* set new group
		 * @param string $nameGroup
		 * @param string $default
		 * @return array
		 */

		public function newGroup($nameGroup, $grouplist = false, $default = 'false', $groupid = false) {
			$getParam = (object)$_POST;

			$valida = $this->access_page('ADMINISTRATION_GROUPS_NEW');

			if ($valida) {

				if ((!empty($nameGroup))) {

					if ($groupid != false) {
						$sql = "UPDATE `admin_groups` SET `group` = '$nameGroup' WHERE `id_group` = '$groupid'";
					} else {
						$sql = sprintf("INSERT INTO `%s` (`group`, `default`) VALUES ('%s', '%s')", $this->table_group, $nameGroup, $default);
					}

					$checkInsert = $this->conexion->query($sql);

					if ($checkInsert) {
						if ($groupid == false) {
							$groupid = $this->conexion->lastInsertId();
						}

						$this->setAudit($_SESSION['uid'], "admin_new_group_ok", "Grupo $nameGroup creado exitosamente");

						$deletePreview = "DELETE FROM `bm_user_host_group` WHERE `id_group` = '$groupid'";
						$deletePreview = $this->conexion->query($deletePreview);

						$insertGroupToHostSQL = 'INSERT INTO `bm_user_host_group` (`id_group`, `groupid`) VALUES ';

						foreach ($grouplist as $key => $value) {
							$valuesGroupToHostSQL[] = "($groupid, $value)";
						}

						$insertGroupToHostRESULT = $this->conexion->query($insertGroupToHostSQL . join(',', $valuesGroupToHostSQL));

						return true;
					} else {
						$this->setAudit($_SESSION['uid'], "admin_new_group_nok", "Error al crear Grupo $nameGroup en la base");
						return false;
					}

				} else {
					$this->setAudit($_SESSION['uid'], "admin_new_group_nok", "Error al crear grupo $nameGroup, por que los campos no corresponden");
					return false;
				}

			} else {
				$this->setAudit($_SESSION['uid'], "admin_new_user_nok", "Error al crear grupo $nameGroup, por que no tiene permisos");
				return false;
			}
		}

		public function deleteGroups($id_groups) {
			if (is_numeric($id_groups)) {

				$valida = $this->access_page('ADMINISTRATION_GROUPS_DELETE');

				if ($valida) {

					$sql = sprintf("DELETE FROM `%s` WHERE `id_group` = '%s';", $this->table_group, $id_groups);
					$checkDelete = $this->conexion->query($sql);
					if ($checkDelete) {
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
		}

		/* get Group All
		 * @return array
		 */

		public function getGroupAll() {
			$valida = $this->access_page('ADMINISTRACION');
			if ($valida) {

				$group_sql = sprintf("SELECT * FROM `%s` WHERE `id_company` > 0 ", $this->table_group);

				$order_by = ' ORDER BY `group`  ASC';

				$group = $this->conexion->queryFetch($group_sql . $order_by);
				return $group;
			} else {
				return false;
			}

		}

		/* get Group User
		 * @return array
		 */

		public function getGroupUser() {
			if (isset($_SESSION['id_group']) && is_numeric($_SESSION['id_group'])) {
				return $_SESSION['id_group'];
			} else {
				return false;
			}
		}

		/*
		 * Get user is admin
		 * @return boolean
		 */

		public function is_admin() {
			if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true) {
				return true;
			} else {
				return false;
			}
		}

		/*
		 * Function to check if a user is secret
		 * @return boolean
		 */

		public function isSecret() {
			if (isset($_SESSION['secretadmin']) && $_SESSION['secretadmin'] == true) {
				return true;
			} else {
				return false;
			}
		}

		public function getLang() {
			if (isset($_SESSION['language'])) {
				return $_SESSION['language'];
			} else {
				return 'es';
			}
		}

		/* Deprecate */

		public function updateAttemptClock() {
			if (isset($_SESSION['uid'])) {

				if (time() > $_SESSION['timeNextRefresh']) {
					$this->refreshVarSession();
				}

				$update_user = "UPDATE `admin_users` SET `attempt_clock` = NOW() WHERE `id_user` = " . $_SESSION['uid'];
				$this->conexion->query($update_user, false, $this->logs_name);
			}
		}

		public function level_user() {

			$sql_is_admin = sprintf("SELECT `id_group` FROM `%s` WHERE `id_user` = '%s'", $this->table_user, $_SESSION['uid']);
			$level = $this->conexion->queryFetch($sql_is_admin);
			if ($level) {

				if ($level[0]['id_group'] == 23) {
					return 'op';
				} elseif ($level[0]['id_group'] == 24) {
					return 'super';
				} else {
					return false;
				}
			} else {
				return true;
			}
		}

		/* Extras method
		 *
		 */

		public function fixActiviti() {

			$getHistoryFix_sql = "SELECT H.`id_history`,H.`id_sessions`,H.`uid`, H.`unixtimestart`, H.`unixtimeactiviti`,  ( H.`unixtimeactiviti` - H.`unixtimestart` )  as  duracion, U.`id_empresa` , FROM_UNIXTIME( H.`unixtimeactiviti`) as Fecha
			FROM `$this->table_session_history` H 
			LEFT JOIN `admin_users` U ON U.`id_user`=H.`uid`   
			WHERE H.`status` = 'pendiente' AND U.`id_group` = 23";

			$getHistoryFix_result = $this->conexion->queryFetch($getHistoryFix_sql, $this->logs_name);

			if ($getHistoryFix_result) {

				$this->conexion->InicioTransaccion();
				$status = true;
				foreach ($getHistoryFix_result as $key => $value) {

					$sql = "INSERT INTO `Historico` ( `id_operador`, `id_empresa`, `id_user`, `type`, `text`, `fechahora`,`duracion`,`id_sessions`)
			   				VALUES (" . $value['uid'] . ", " . $value['id_empresa'] . ", 0, 'status_operator', 'Actividad', '" . $value['Fecha'] . "', " . $value['duracion'] . "," . $value['id_sessions'] . ")";

					$query = $this->conexion->query($sql, false, $this->logs_name);

					if (!$query) {
						$status = false;
					}

					$sql = sprintf("SELECT `id_historico` , `text` , %d -  UNIX_TIMESTAMP(`fechahora`)  AS unixtimeend, `duracion`, `id_sessions` 
						FROM `Historico` WHERE `id_sessions` = %d  AND `text` != 'Actividad' ORDER BY `id_historico` DESC LIMIT 1", $value['unixtimeactiviti'], $value['id_sessions']);

					$history = $this->conexion->queryFetch($sql);

					if ($history) {

						$history = $history[0];

						$this->logs->debug("Registrando estado anterior.", $history);

						$update_sql = sprintf("UPDATE `Historico` SET `duracion` = %d WHERE (`id_historico`=%d) LIMIT 1", $history['unixtimeend'], $history['id_historico']);
						$valid = $this->conexion->query($update_sql);

						if (!$valid) {
							$status = false;
						}

					}

					$update_sql = "UPDATE `$this->table_session_history` SET `status` = 'close' WHERE (`id_history`=" . $value['id_history'] . ") LIMIT 1";
					$valid_update = $this->conexion->query($update_sql);

					if (!$valid_update) {
						$status = false;
					}
				}

				if ($status) {
					$this->conexion->commit();
				} else {
					$this->conexion->rollBack();
				}
			}
		}

		/*
		 * Set audit info
		 * @param integer $uid
		 * @param string $action
		 * @param string $txt
		 * @return boolean
		 */

		public function setAudit($uid, $action, $txt) {

			if (empty($uid)) {
				$uid = 0;
			}

			$sql = "INSERT INTO `" . $this->table_audit_user . "` (`id_user`, `fechahora`, `action`, `details`) VALUES (?, NOW(), ?, ?)";

			$query = $this->conexion->prepare($sql);

			$query->bindParam(1, $uid, PDO::PARAM_INT);
			$query->bindParam(2, $action, PDO::PARAM_STR);
			$query->bindParam(3, $txt, PDO::PARAM_STR);

			$data = $this->conexion->execute($query);

			if ($data) {
				return true;
			} else {
				return false;
			}
		}

		private function getlicense() {
			//$UrlLicense = "bmonitor.baking.cl:3362/license.php";
			$UrlLicense = "http://license.baking.cl/license.php";

			$license_company = $this->parametro->get("license_company", 'EMPTY_COMPANY');

			$license_code = $this->parametro->get("license_code", 'EMPTY_CODE');

			$result_xml = new SimpleXMLElement('<?xml version="1.0" standalone="yes"?><licence></licence>');

			$result_xml->addChild('license_company', $license_company);

			$result_xml->addChild('license_code', $license_code);

			$result_xml_encryp = $this->encrypted($result_xml->asXML());

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_URL, $UrlLicense);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
			curl_setopt($ch, CURLOPT_TIMEOUT, 4);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $result_xml_encryp);

			$result_encrypt = curl_exec($ch);

			if ($result_encrypt) {
				return $result_encrypt;
			} else {
				return false;
			}
		}

		public function getlicenseXML() {
			$license_encrypt = $this->getlicense();

			$license = $this->loadXML($this->descrypt($license_encrypt));

			if ($license && isset($license->Expiration)) {
				return $license_encrypt;
			} else {
				return false;
			}
		}

		public function license() {
			return true;

			$file = ETC . "license.lic";
			$cache_time = 86400;

			if (file_exists($file)) {

				if ((fileatime($file) + $cache_time) > time()) {

					$fp = fopen($file, "r");

					if ($fp) {
						$license_encrypt = fread($fp, filesize($file));

						if ($license_encrypt) {
							$license = $this->loadXML($this->descrypt($license_encrypt));

							if ($license) {

								if (isset($license->Expiration)) {

									$unix = strtotime($license->Expiration);

									if ($unix > time()) {

										$result = array(
											"FDT" => $license->FDT,
											"NEUTRALIDAD" => $license->Neutralidad
										);

										return $result;

									} else {
										fclose($fp);
										return false;
									}

								} else {
									fclose($fp);
									return false;
								}
							} else {

								fclose($fp);
								return false;
							}

						} else {
							fclose($fp);
							return false;
						}
					} else {
						return false;
					}

				} else {
					$license_encrypt = $this->getlicenseXML();

					if ($license_encrypt) {

						$license = $this->loadXML($this->descrypt($license_encrypt));

						if ($license) {

							$fp = fopen($file, "w+");
							if ($fp) {
								fwrite($fp, $license_encrypt . PHP_EOL);
								fclose($fp);

								if (isset($license->Expiration)) {

									$unix = strtotime($license->Expiration);

									if ($unix > time()) {

										$result = array(
											"FDT" => $license->FDT,
											"NEUTRALIDAD" => $license->Neutralidad
										);

										return $result;

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

					} else {
						if (fileatime($file) > $license->CheckLicense) {

							$fp = fopen($file, "r");

							if ($fp) {
								$license_encrypt = fread($fp, filesize($file));

								if ($license_encrypt) {
									$license = $this->loadXML($this->descrypt($license_encrypt));

									if ($license) {

										if (isset($license->Expiration)) {

											$unix = strtotime($license->Expiration);

											if ($unix > time()) {

												$result = array(
													"FDT" => $license->FDT,
													"NEUTRALIDAD" => $license->Neutralidad
												);

												return $result;

											} else {
												fclose($fp);
												return false;
											}

										} else {
											fclose($fp);
											return false;
										}
									} else {

										fclose($fp);
										return false;
									}

								} else {
									fclose($fp);
									return false;
								}
							} else {
								return false;
							}
						} else {
							return false;
						}
					}
				}

			} else {
				$license_encrypt = $this->getlicenseXML();

				if ($license_encrypt) {

					$license = $this->loadXML($this->descrypt($license_encrypt));

					if ($license) {

						$fp = fopen($file, "w+");
						if ($fp) {
							fwrite($fp, $license_encrypt . PHP_EOL);
							fclose($fp);

							if (isset($license->Expiration)) {

								$unix = strtotime($license->Expiration);

								if ($unix > time()) {

									$result = array(
										"FDT" => $license->FDT[0],
										"NEUTRALIDAD" => $license->Neutralidad[0]
									);

									return $result;

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

				} else {
					return false;
				}

			}

		}

		function __destruct() {
			if ($this->loginStatus == false) {
				//session_destroy();
			}
		}

	}

}
?>