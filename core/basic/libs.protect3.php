<?php
	session_start();
	class Protect3 extends Basic
	{
			var $group_id;
			var $service_name;
			var $user_name;
			public  $user_id;
			var $table_user = "bm_users";
			var $table_group = "bm_groups";
			var $table_section = "bm_section";
			var $table_group_profile = "bm_groups_profile";
			var $table_audit_user = "bm_auditlog";
			var $audit_user = true;
			
			function __construct($parametro,$conexion,$logs,$language)  {
				$this->parametro = $parametro;
				$this->conexion = $conexion;
				$this->logs = $logs;
				$this->language = $language;
				ini_set("session.gc_maxlifetime", "1"); 
				session_cache_limiter('private');
				session_cache_expire(1);	
			}
			
			
			public function  login($user = '', $password = ''){
				if ($user != "" && $password != "") {
					if ($user = $this->check_user('login',$user,$password)) {
						$this->reg_event($user->id_user,'login',"Acceso exitoso");
						$redirect = $this->set_user($user->id_user,$user->name,$user->id_group);
						$jsondata['valid'] = true;
						$jsondata['redirect'] =  $redirect;
						return $this->iso_json_encode($jsondata);
					} else {
						$jsondata['valid'] = false;
						$jsondata['error'] =  $this->language->LOGIN_USER_INCORRECT;
						return $this->iso_json_encode($jsondata);
					}
				} else {
					$jsondata['valid'] = false;
					$jsondata['error'] = $this->language->LOGIN_DATE_EMPTY;
					return $this->iso_json_encode($jsondata);
				}
			}
			
			public function access_page($level=false)
			{
				if ((isset($_SESSION['active'])) && ($_SESSION['active'] = true)) {
					
					$is_admin = $this->is_admin();
					
					if($is_admin) {
						return true;
					}
					
					if($level) {
						if(isset($_SESSION['permisos'])) {
							$clave = in_array("$level", $_SESSION['permisos'],true);
							if($clave) {
								return true;
							} else {
								return false;
							}					
						} else {
							header( "Location: login" ) ;
						}		
					} else {
						return true;
					}
	
				} else {
					
					header( "Location: login" ) ;
				}
			}
			
			public function is_admin(){
				$sql_is_admin = "SELECT `is_admin` FROM `bm_users` WHERE `id_user`=".$_SESSION['iduser'];
				$is_admin = $this->conexion->queryFetch($sql_is_admin);
				if($is_admin){
					
					if($is_admin[0]['is_admin'] == "true") {
						return true;
					} else {
						return false;
					}
					
				} else {
					return true;
				}
			}
			
			public function getIDUser(){
				$valida = $this->access_page();
				
				if($valida) {
					return $_SESSION['iduser'];
				} else {
					return false;
				}
			}
			
			
			public function getGroupUser(){
				$valida = $this->access_page();
				
				if($valida) {
					return $_SESSION['group'];
				} else {
					return false;
				}
			}
						
			private function sectionCache($id_user)
			{
				if(!empty($id_user)) {
					$valida_section_sql = "SELECT `bm_section`.`section` FROM `bm_users`  
						JOIN `bm_groups_profile`  USING(`id_group`)
						JOIN `bm_section` ON `bm_groups_profile`.`id_section`=`bm_section`.`id_section`
						WHERE `bm_users`.`id_user`=$id_user";
					$valida_section = $this->conexion->queryArray($valida_section_sql,true);
					if($valida_section) {
						$_SESSION['permisos'] = array();
						$_SESSION['permisos'] = $valida_section;
					} else {
						return false;
					}
					
				} else {
					return false;
				}
			}
			
			public function log_out() {
				if(isset($_SESSION['iduser'])) {
					$this->reg_event($_SESSION['iduser'],'logout',"Salida exitosa");
				}
				$_SESSION = array();
				//session_destroy();
				$url = URL_BASE."login";
				header( "Location: $url" ) ;
			}
			
			public function getTime($value='')
			{
				$time = ( time() - $_SESSION['last_access'] );
				return $time;
			}
				
			private function set_user($iduser,$name,$group) {
				
				$session_id=session_id();
				$visit_sql = sprintf("UPDATE `%s` SET  `attempt_clock` = NOW() ,`attempt_seccion` = '%s' WHERE `id_user` = '%s'", 
										$this->table_user, 
										$session_id,
										$iduser);
				$result = $this->conexion->query($visit_sql);
				
				$_SESSION['iduser'] = $iduser;
				$_SESSION['group'] = $group;
				$_SESSION['name'] = $name;
				$_SESSION['active'] = true;;
				
				$valida = $this->sectionCache($iduser);
				
				if( !isset($_SESSION['last_access']) || (time() - $_SESSION['last_access']) > 60 ) 
  					$_SESSION['last_access'] = time();
				if (isset($_SESSION['referer']) && $_SESSION['referer'] != "") {
					$next_page = $_SESSION['referer'];
					unset($_SESSION['referer']);
				} else {
					$next_page = URL_BASE;
				}
				if(empty($next_page)) $next_page = "";
				return $next_page; 
			}
			
			public function reg_event($userid,$action,$txt) {
				if ($this->audit_user) {
					$visit_sql = sprintf("INSERT INTO `%s` (`user_id`, `fechahora`, `action`, `details`) VALUES ('%s', NOW(), '%s', '%s')", 
											$this->table_audit_user, 
											$userid, 
											$action, 
											$txt);
					$result = $this->conexion->query($visit_sql);
					if($result) {
						return true;
					} else {
						return false;
					}	
				} else {
					return true;
				}
			}
			
			private function check_user($pass = "login",$user,$pass) {
				switch ($pass) {
					case "new": 
					$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE user_email = '%s' OR user_name = '%s'", $this->table_name, $this->user_email, $this->user);
					break;
					case "lost":
					$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE user_email = '%s' AND active = 'y'", $this->table_name, $this->user_email);
					break;
					case "new_pass":
					$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE user_pass = '%s' AND user_id = %d", $this->table_name, $this->user_pw, $this->id);
					break;
					case "active":
					$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE user_id = %d AND active = 'n'", $this->table_name, $this->id);
					break;
					case "validate":
					$sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE user_id = %d AND user_mail_tmp <> ''", $this->table_name, $this->id);
					break;
					default:
					$password = (strlen($pass) < 32) ? sha1($pass) : $pass;
					$sql = sprintf("SELECT COUNT(*) AS test , `id_user`,  `name` ,`lifetime`, `id_group` FROM %s WHERE BINARY user = '%s' AND passwd = '%s' AND active = 'true'", $this->table_user,$user, $password);
				}

				$result = $this->conexion->queryFetch($sql);

				if ($result[0]['test'] == 1) {
					return (object)$result[0];
				} else {
					return false;
				}
			}
			
			
	}
?>