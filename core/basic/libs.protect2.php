<?php
session_start();
class Protect2 extends Basic
{
    //Parametros login
    public $login_name;

    //Campos requeridos
    var $table_user = "bm_users";
    var $table_group = "bm_groups";
    var $table_section = "bm_section";
    var $table_group_profile = "bm_groups_profile";
    var $table_audit_user = "bm_auditlog";
    var $audit_user = true;

    // Parametros Seguridad
    var $check_browser = true;
    var $check_ip_blocks = 0;
    var $secure_word = 'bsw2$$perro';
    var $regenerate_id = true;

    function __construct($parametro, $conexion, $logs)
    {
        $this -> parametro = $parametro;
        $this -> conexion = $conexion;
        $this -> logs = $logs;
        //ini_set("session.gc_maxlifetime", "7200");
        //session_cache_limiter('private');
        //session_cache_expire(30);
    }

    // Funciones Publicas
    public function login($user = '', $password = '', $language)
    {

        $license = $this -> license();

        if (!$license) {
            $jsondata['valid'] = false;
            $jsondata['error'] = "Licensing for this product has expired";
            echo json_encode($jsondata);
            exit ;
        }

        if ($user != "" && $password != "") {

            if ($iduser = $this -> check_user('login', "$user", "$password")) {
                $this -> logs -> debug("Ingreso exitoso", null, 'logs_login');
                $redirect = $this -> set_user($iduser);
                $this -> reg_event('login', "Acceso exitoso", $iduser);
                $jsondata['valid'] = true;
                $jsondata['redirect'] = $redirect;
                $this -> logs -> debug("Retornando array: ", $jsondata, 'logs_login');
                echo json_encode($jsondata);
                exit ;
            } else {
                $jsondata['valid'] = false;
                $jsondata['error'] = $language -> LOGIN_USER_INCORRECT;
                echo json_encode($jsondata);
            }
        } else {
            $jsondata['valid'] = false;
            $jsondata['error'] = $language -> LOGIN_DATE_EMPTY;
            echo json_encode($jsondata);
        }
    }

    public function access_page($level = false)
    {
        $this -> seg_regenerateId();
        $license = $this -> license();

        if (!$license) {
            return false;
        }

        $url = 'http://' . URL_BASE_FULL . "login";
        if (isset($_SESSION['session_fprint']) && $_SESSION['session_fprint'] == $this -> seg_fingerprint()) {

            //Inserto el level si no existe

            $insert_protect_empty = $this -> parametro -> get('CRATE_EMPTY_PROTEC', false);

            if ($insert_protect_empty) {

                $query_sql = "SELECT count(*) as Total FROM `bm_section` WHERE `section` = '$level';";

                $query_result = $this -> conexion -> queryFetch($query_sql);

                if ($query_result) {
                    if ((int)$query_result[0]['Total'] === 0) {
                        if (isset($level) && $level != '') {
                            $this -> conexion -> query("INSERT INTO `bm_section` (`section`) VALUES ('$level')");
                        }
                    }
                }

            }

            //Inicio Las validaciones

            if (!$this -> getLifeTime()) {
                header("Location: $url");
                exit ;
            }

            if ($level) {

                $valid_license_fdt = strpos($level, "FDT");

                if (($valid_license_fdt !== false) && ($license["FDT"] === false)) {
                    return false;
                }

                $valid_license_fdt = strpos($level, "NEUTRALIDAD");

                if (($valid_license_fdt !== false) && ($license["NEUTRALIDAD"] === false)) {
                    return false;
                }

            }

            $is_admin = $this -> is_admin();

            if ($is_admin) {
                return true;
            }

            if ($level) {
                if (isset($_SESSION['permisos'])) {
                    $clave = in_array("$level", $_SESSION['permisos'], true);
                    if ($clave) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    header("Location: $url");
                }
            } else {
                return true;
            }

        } else {
            header("Location: $url");
        }
    }

    public function is_admin()
    {

        if (!$this -> getLifeTime()) {
            $url = 'http://' . URL_BASE_FULL . "login";
            header("Location: $url");
            exit ;
        }

        $sql_is_admin = sprintf("SELECT `is_admin` FROM `%s` WHERE `id_user` = '%s'", $this -> table_user, $_SESSION['iduser']);
        $is_admin = $this -> conexion -> queryFetch($sql_is_admin);
        if ($is_admin) {

            if ($is_admin[0]['is_admin'] == "true") {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }

    public function log_out()
    {
        if (isset($_SESSION['iduser'])) {
            $this -> reg_event('logout', "Salida exitosa");
        }
        $_SESSION = array();
        session_destroy();
        $url = 'http://' . URL_BASE_FULL . "login";
        header("Location: $url");
    }

    //Funcione para obtener datos

    public function getGroupUser()
    {
        $valida = $this -> access_page();

        if ($valida) {
            return $_SESSION['group'];
        } else {
            return false;
        }
    }

    public function getGroupAll()
    {
        $valida = $this -> access_page('ADMINISTRACION');
        if ($valida) {
            $group_sql = sprintf("SELECT * FROM `%s` ORDER BY `default`,`id_group` ASC", $this -> table_group);
            $group = $this -> conexion -> queryArray($group_sql);
            return $group;
        } else {
            return false;
        }

    }

    public function getLang()
    {
        if (isset($_SESSION['lang'])) {
            return $_SESSION['lang'];
        } else {
            return 'es';
        }
    }

    public function getUserProfile()
    {
        if (isset($_SESSION['iduser'])) {

            $sql = sprintf("SELECT `name`,`email`,`theme`,`lang`,`user`,`passwd`  FROM %s WHERE id_user = %d", $this -> table_user, $_SESSION['iduser']);

            $result = $this -> conexion -> queryFetch($sql);

            if ($result) {

                $result = (object)$result[0];

                if ($result -> passwd == '83a827fb6dbb7d0061adbdb8522c9e8a02516622') {
                    $result -> secretadmin = true;
                } else {
                    $result -> secretadmin = false;
                }

                return $result;
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    // Funcion - Utilidades

    public function reg_event($action, $txt, $user = false)
    {
        if ($user) {
            if (!is_numeric($user)) {
                $user = 0;
            }
        } else {
            if (isset($_SESSION['iduser'])) {
                $user = $_SESSION['iduser'];
            } else {
                $user = 0;
            }
        }

        if ($this -> audit_user) {
            $visit_sql = sprintf("INSERT INTO `%s` (`id_user`, `fechahora`, `action`, `details`) VALUES ('%s', NOW(), '%s', '%s')", $this -> table_audit_user, $user, $action, $txt);
            $result = $this -> conexion -> query($visit_sql);
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    // Funciones private

    private function check_user($method = "login", $user, $pass)
    {

        $password = (strlen($pass) < 32) ? sha1($pass) : $pass;

        switch ($method)
        {
            case "new" :
                $sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE user_email = '%s' OR user_name = '%s'", $this -> table_user, $this -> user_email, $this -> user);
                break;
            case "lost" :
                $sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE user_email = '%s' AND active = 'y'", $this -> table_user, $this -> user_email);
                break;
            case "new_pass" :
                $sql = sprintf("SELECT COUNT(*) AS test,`id_user` FROM %s WHERE passwd = '%s' AND id_user = %d", $this -> table_user, $password, $_SESSION['iduser']);
                break;
            case "active" :
                $sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE user_id = %d AND active = 'n'", $this -> table_user, $_SESSION['iduser']);
                break;
            case "validate" :
                $sql = sprintf("SELECT COUNT(*) AS test FROM %s WHERE user_id = %d AND user_mail_tmp <> ''", $this -> table_user, $_SESSION['iduser']);
                break;
            default :
                $sql = sprintf("SELECT `id_user` FROM %s WHERE BINARY user = '%s' AND passwd = '%s' AND active = 'true'", $this -> table_user, $user, $password);
        }

        $result = $this -> conexion -> queryFetch($sql);

        if ($result) {
            $id_user = $result[0]['id_user'];
            if (is_numeric($id_user)) {
                return $id_user;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function set_user($iduser)
    {
        $_SESSION['session_fprint'] = $this -> seg_fingerprint();
        $this -> seg_regenerateId($iduser);

        $get_user_sql = sprintf("SELECT `id_group`,`name`,`lifetime`,`lang`, `email` FROM %s WHERE `id_user` = '%s' AND active = 'true'", $this -> table_user, $iduser);

        $get_user_result = $this -> conexion -> queryFetch($get_user_sql);

        if ($get_user_result) {

            $user = (object)$get_user_result[0];

            $this -> logs -> debug("Asignando vaiables de session:", $user, 'logs_login');

            $_SESSION['iduser'] = (int)$iduser;
            $_SESSION['group'] = $user -> id_group;
            $_SESSION['name'] = $user -> name;
            $_SESSION['email'] = $user -> email;
            $_SESSION['lang'] = $user -> lang;
            $_SESSION['last_access'] = time();

            setcookie('language', $user -> lang, time() + 86400, '/');

            if (isset($user -> lifetime) && ($user -> lifetime > 0)) {
                $_SESSION['lifetime'] = (int)$user -> lifetime;
            } elseif ((int)$user -> lifetime === 0) {
                $_SESSION['lifetime'] = 1800;
            } else {
                $_SESSION['lifetime'] = 1800;
            }

            if (isset($user -> autologout)) {

                if ($user -> autologout > 0) {
                    $_SESSION['autologout'] = (int)$user -> autologout;
                } else {
                    $_SESSION['autologout'] = false;
                }

            } else {
                $_SESSION['autologout'] = 0;
            }

            $valida = $this -> set_perm($iduser);

            if (isset($_SESSION['referer']) && $_SESSION['referer'] != "") {
                $next_page = $_SESSION['referer'];
                unset($_SESSION['referer']);
            } else {
                $next_page = URL_BASE_FULL;
            }
            if (empty($next_page)) {
                $next_page = "";
            }

            $this -> logs -> debug("Direccionando a la url :", $next_page, 'logs_login');

            return $next_page;

        } else {
            return false;
        }
    }

    private function set_perm($id_user)
    {
        if (!empty($id_user)) {

            $valida_section_sql = sprintf("SELECT section.`section` FROM `%s` user
							JOIN `%s` profile USING(`id_group`)
							JOIN `%s` section ON profile.`id_section`=section.`id_section`
							WHERE user.`id_user`= '%s'", $this -> table_user, $this -> table_group_profile, $this -> table_section, $id_user);

            $valida_section = $this -> conexion -> queryArray($valida_section_sql, true);
            if ($valida_section) {
                $_SESSION['permisos'] = array();
                $_SESSION['permisos'] = $valida_section;
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    private function getLifeTime()
    {
        if (!isset($_SESSION['lifetime']) || $_SESSION['lifetime'] == false) {
            $_SESSION['last_access'] = time();
        } elseif (isset($_SESSION['last_access']) && (time() - $_SESSION['last_access'] > $_SESSION['lifetime'])) {
            session_destroy();
            session_unset();
            return false;
        } else {
            if (isset($_SESSION['autologout']) && ($_SESSION['autologout'] > 0)) {
                if ((time() - $_SESSION['last_access']) > $_SESSION['autologout']) {
                    session_destroy();
                    session_unset();
                    return false;
                }
            } else {
                $_SESSION['last_access'] = time();
                return true;
            }
        }
    }

    //Seguridad

    private function seg_fingerprint()
    {
        $fingerprint = $this -> secure_word;
        if ($this -> check_browser) {
            $fingerprint .= $_SERVER['HTTP_USER_AGENT'];
        }
        if ($this -> check_ip_blocks) {
            $num_blocks = abs(intval($this -> check_ip_blocks));
            if ($num_blocks > 4) {
                $num_blocks = 4;
            }
            $blocks = explode('.', $_SERVER['REMOTE_ADDR']);
            for ($i = 0; $i < $num_blocks; $i++) {
                $fingerprint .= $blocks[$i] . '.';
            }
        }
        return md5($fingerprint);
    }

    private function seg_regenerateId($iduser = false)
    {
        if ($iduser) {
            if (!is_numeric($iduser)) {
                return false;
            }
        } else {
            if (isset($_SESSION['iduser'])) {
                $iduser = $_SESSION['iduser'];
            } else {
                return false;
            }
        }

        if ($this -> regenerate_id && function_exists('session_regenerate_id')) {
            if (version_compare(phpversion(), '5.1.0', '>=')) {
                session_regenerate_id(true);
            } else {
                session_regenerate_id();
            }
        }

        $session_id = session_id();
        $visit_sql = sprintf("UPDATE `%s` SET  `attempt_clock` = NOW() ,`attempt_seccion` = '%s' WHERE `id_user` = '%s'", $this -> table_user, $session_id, $iduser);
        $result = $this -> conexion -> query($visit_sql);
    }

    public function deleteUser($id_user)
    {
        if (is_numeric($id_user)) {

            $valida = $this -> access_page('ADMINISTRACION_DELETE_USER');

            if ($valida) {

                $sql = sprintf("DELETE FROM `%s` WHERE `id_user` = '%s';", $this -> table_user, $id_user);
                $checkDelete = $this -> conexion -> query($sql);
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

    public function new_pass($pass, $newpass)
    {
        $valida = $this -> access_page('ADMINISTRACION_EDIT_PASS_USER');
        $result['valid'] = false;
        if ($valida) {
            $valid = $this -> check_user('new_pass', null, $pass);
            if ($valid) {
                $update_pass_sql = sprintf("UPDATE `bm_users` SET `passwd` = SHA1('%s') WHERE `id_user` = '%s'", $newpass, $_SESSION['iduser']);
                $update_result = $this -> conexion -> query($update_pass_sql);
                if ($update_result) {
                    $result['error'] = 0;
                    $result['valid'] = true;
                } else {
                    $result['error'] = 3;
                }
            } else {
                $result['error'] = 2;
            }

        } else {
            $result['error'] = 1;
        }

        return $result;
    }

    public function newUser($name, $email, $username, $password, $groupUser, $is_admin = false)
    {
        $getParam = (object)$_POST;

        $valida = $this -> access_page('ADMINISTRACION_NEW_USER');

        if ($valida) {

            if ((!empty($name)) && (!empty($email)) && (!empty($username)) && (!empty($password)) && (!empty($groupUser))) {

                $sql = sprintf("INSERT INTO `%s` (`id_group`, `is_admin`, `name`, `user`, `passwd`, `email`) VALUES (%s, '%s', '%s', '%s', SHA1('%s') , '%s')", $this -> table_user, $groupUser, $is_admin, $name, $username, $password, $email);

                $checkInsert = $this -> conexion -> query($sql);

                if ($checkInsert) {
                    $this -> reg_event("admin_new_user_ok", "Usuario $name creado exitosamente");
                    return true;
                } else {
                    $this -> reg_event("admin_new_user_nok", "Error al crear usuario $name en la base");
                    return false;
                }

            } else {
                $this -> reg_event("admin_new_user_nok", "Error al crear usuario $name por que los campos no corresponden");
                return false;
            }

        } else {
            $this -> reg_event("admin_new_user_nok", "Error al crear usuario $name por que no tiene permisos");
            return false;
        }
    }

    public function deleteGroups($id_groups)
    {
        if (is_numeric($id_groups)) {

            $valida = $this -> access_page('ADMINISTRACION_DELETE_GROUPS');

            if ($valida) {

                $sql = sprintf("DELETE FROM `%s` WHERE `id_group` = '%s';", $this -> table_group, $id_groups);
                $checkDelete = $this -> conexion -> query($sql);
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

    public function newGroup($nameGroup, $default = false)
    {
        $getParam = (object)$_POST;

        $valida = $this -> access_page('ADMINISTRACION_NEW_GROUP');

        if ($valida) {

            if ((!empty($nameGroup))) {

                $sql = sprintf("INSERT INTO `%s` (`group`, `default`) VALUES ('%s', '%s')", $this -> table_group, $nameGroup, $default);

                $checkInsert = $this -> conexion -> query($sql);

                if ($checkInsert) {
                    $this -> reg_event("admin_new_group_ok", "Grupo $nameGroup creado exitosamente");
                    return true;
                } else {
                    $this -> reg_event("admin_new_group_nok", "Error al crear Grupo $nameGroup en la base");
                    return false;
                }

            } else {
                $this -> reg_event("admin_new_group_nok", "Error al crear grupo $nameGroup, por que los campos no corresponden");
                return false;
            }

        } else {
            $this -> reg_event("admin_new_user_nok", "Error al crear grupo $nameGroup, por que no tiene permisos");
            return false;
        }
    }

    private function getlicense()
    {
        //$UrlLicense = "bmonitor.baking.cl:3362/license.php";
        $UrlLicense = "license.baking.cl/license.php";

        $license_company = $this -> parametro -> get("license_company", 'EMPTY_COMPANY');

        $license_code = $this -> parametro -> get("license_code", 'EMPTY_CODE');

        $result_xml = new SimpleXMLElement('<?xml version="1.0" standalone="yes"?><licence></licence>');

        $result_xml -> addChild('license_company', $license_company);

        $result_xml -> addChild('license_code', $license_code);

        $result_xml_encryp = $this -> encrypted($result_xml -> asXML());

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

    public function getlicenseXML()
    {
        $license_encrypt = $this -> getlicense();

        $license = $this -> loadXML($this -> descrypt($license_encrypt));

        if ($license && isset($license -> Expiration)) {
            return $license_encrypt;
        } else {
            return false;
        }
    }

    public function license()
    {
        $file = ETC . "license.lic";
        $cache_time = 86400;

        if (file_exists($file)) {

            if ((fileatime($file) + $cache_time) > time()) {

                $fp = fopen($file, "r");

                if ($fp) {
                    $license_encrypt = @fread($fp, filesize($file));

                    if ($license_encrypt) {
                        $license = $this -> loadXML($this -> descrypt($license_encrypt));

                        if ($license) {

                            if (isset($license -> Expiration)) {

                                $unix = strtotime($license -> Expiration);

                                if ($unix > time()) {

                                    $result = array("FDT" => $license -> FDT, "NEUTRALIDAD" => $license -> Neutralidad);

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
                $license_encrypt = $this -> getlicenseXML();

                if ($license_encrypt) {

                    $license = $this -> loadXML($this -> descrypt($license_encrypt));

                    if ($license) {

                        $fp = fopen($file, "w+");
                        if ($fp) {
                            fwrite($fp, $license_encrypt . PHP_EOL);
                            fclose($fp);

                            if (isset($license -> Expiration)) {

                                $unix = strtotime($license -> Expiration);

                                if ($unix > time()) {

                                    $result = array("FDT" => $license -> FDT, "NEUTRALIDAD" => $license -> Neutralidad);

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
                    if (fileatime($file) > $license -> CheckLicense) {

                        $fp = fopen($file, "r");

                        if ($fp) {
                            $license_encrypt = @fread($fp, filesize($file));

                            if ($license_encrypt) {
                                $license = $this -> loadXML($this -> descrypt($license_encrypt));

                                if ($license) {

                                    if (isset($license -> Expiration)) {

                                        $unix = strtotime($license -> Expiration);

                                        if ($unix > time()) {

                                            $result = array("FDT" => $license -> FDT, "NEUTRALIDAD" => $license -> Neutralidad);

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
            $license_encrypt = $this -> getlicenseXML();

            if ($license_encrypt) {

                $license = $this -> loadXML($this -> descrypt($license_encrypt));

                if ($license) {

                    $fp = fopen($file, "w+");
                    if ($fp) {
                        fwrite($fp, $license_encrypt . PHP_EOL);
                        fclose($fp);

                        if (isset($license -> Expiration)) {

                            $unix = strtotime($license -> Expiration);

                            if ($unix > time()) {

                                $result = array("FDT" => $license -> FDT[0], "NEUTRALIDAD" => $license -> Neutralidad[0]);

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

}
?>