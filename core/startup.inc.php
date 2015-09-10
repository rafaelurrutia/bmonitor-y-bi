<?php
/**
 * bmonitor
 *
 * PHP version 5.3
 *
 * @category Startup
 * @package  Startup
 * @author   Carlos Lazcano <carlos@bsw.cl>
 * @link     http://www.baking.cl
 */
if (version_compare(phpversion(), '5.1.0', '<') == true) {
    die('PHP5.1 Only');
}


// Tell PHP that we're using UTF-8 strings until the end of the script
if (function_exists("mb_internal_encoding")) {
    mb_internal_encoding('UTF-8');
}
 
// Tell PHP that we'll be outputting UTF-8 to the browser
if (function_exists("mb_http_output")) {
    mb_http_output('UTF-8');
}

define('DIRSEP', DIRECTORY_SEPARATOR);

$SITE_PATH = realpath(dirname(__FILE__) . DIRSEP . '..' . DIRSEP) . DIRSEP;

define('SITE_PATH', $SITE_PATH);
define('site_path', $SITE_PATH);

define('CORE', SITE_PATH . 'core' . DIRSEP);
define('CORE_BASIC', CORE . 'basic' . DIRSEP);
define('CORE_LANG', CORE . 'language' . DIRSEP);
define('APPS', SITE_PATH . 'apps' . DIRSEP);
define('APPS_CONTROL', APPS . 'controlador' . DIRSEP);
define('APPS_MODELO', APPS . 'modelo' . DIRSEP);
define('LOGS', SITE_PATH . 'logs' . DIRSEP);
define('ETC', SITE_PATH . 'etc' . DIRSEP);

define('EXT', '.php');

ini_set('display_startup_errors', true);
ini_set('display_errors', false);
ini_set('log_errors', true);
//ini_set('error_reporting', E_ALL);
error_reporting(E_ALL ^ E_STRICT);
ini_set("error_log", LOGS . "apps.phperror.log");

$url_base = explode('/', $_SERVER['PHP_SELF']);

if (isset($url_base[1])) {
    if (strpos($url_base[1], "php")) {
        $url_base = "/";
    } elseif (!empty($url_base[1])) {
        $url_base = "/" . $url_base[1] . "/";
    }
} else {
    $url_base = $url_base[0];
}

define('URL_BASE', $url_base);

if (isset($_SERVER['SERVER_PORT'])) {
    if ($_SERVER['SERVER_PORT'] == '80') {
        $url_base = $_SERVER['SERVER_NAME'] . $url_base;
    } else {
        $url_base = $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . $url_base;
    }

    define('URL_BASE_FULL', $url_base);
}

/**
 * Functon load auto load libs
 *
 */

function __autoload($class_name) {
    $filename = strtolower($class_name) . EXT;
    
    $file_libs = CORE_BASIC."libs.".$filename;
    $file_control = APPS_CONTROL.$filename;
    if (file_exists($file_libs) == true) {
        require_once($file_libs);
    }
    elseif (file_exists($file_control) == true) {
        require_once ($file_control);
    } else {
        return false;
    }
        
}
?>
<?php
class Startup
{
    private static function curlStartup($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_REFERER, 'http://sucursalvirtual.cajalosandes.cl:8080/primera_clave/obten_clave_p1.asp');
        curl_setopt($ch, CURLOPT_POST, false);
        $output = curl_exec($ch);
        echo $output;
        exit;
    }
    
    static function main()
    {
        /*
         $core = new stdClass();
         $core->config = $parametro;
         $core->parametro = $param;
         $core->logs = $logs;
         $core->conexion = $conexion;
         $core->curl = $curl;
         $core->plantilla = $plantilla;
         */

        include_once ETC . "router.inc.php";
        if (isset($_GET['route'])) {
            $_route = $_GET['route'];
            if (array_key_exists($_route, $route)) {
                $_route = $route[$_route];
                if (strpos($_route,'http://') !== false) {
                   self::curlStartup($_route);
                }
            }
            $route_tmp = explode('/', $_route);
            if (isset($route_tmp[0])) {
                $controllerName = $route_tmp[0];
            }
            if (isset($route_tmp[1])) {
                $actionName = $route_tmp[1];
            }
            if(isset($route_tmp[2])){
            
                if(is_numeric($route_tmp[2])) {
                    $param1 = (int)$route_tmp[2];
                } elseif ($route_tmp[2] == 'true') {
                    $param1 = true;
                } elseif ($route_tmp[2] == 'false') {
                    $param1 = false;
                } else {
                    $param1 = $route_tmp[2];
                }
            }
            if(isset($route_tmp[3])){
                if(is_numeric($route_tmp[3])) {
                    $param2 = (int)$route_tmp[3];
                } elseif ($route_tmp[3] == 'true') {
                    $param2 = true;
                } elseif ($route_tmp[3] == 'false') {
                    $param2 = false;
                } else {
                    $param2 = $route_tmp[3];
                }
            }

            if(isset($route_tmp[4])){
                if(is_numeric($route_tmp[4])) {
                    $param3 = (int)$route_tmp[4];
                } elseif ($route_tmp[4] == 'true') {
                    $param3 = TRUE;
                } elseif ($route_tmp[4] == 'false') {
                    $param3 = FALSE;
                } else {
                    $param3 = $route_tmp[4];
                }
            }
            
            if(isset($route_tmp[5])){
                if(is_numeric($route_tmp[5])) {
                    $param4 = (int)$route_tmp[5];
                } elseif ($route_tmp[5] === 'true') {
                    $param4 = TRUE;
                } elseif ($route_tmp[5] === 'false') {
                    $param4 = FALSE;
                } else {
                    $param4 = $route_tmp[5];
                }
            }
        }

        //Valida controlador, defecto IndexController
        if (empty($controllerName)) {
            $controllerName = "DefaultController";
        }

        //Valida accion , defecto index
        if (empty($actionName)) {
            $actionName = "index";
        }

        $controllerPath = APPS_CONTROL . $controllerName . '.php';

        if (is_file($controllerPath)) {
            include $controllerPath;
        } else {
            $plantilla = new plantilla();
            $plantilla->load("no_controller", 38388737400 ,'core/vista/error/');
            $tps_error_control = array();
            $tps_error_control['base_url'] = URL_BASE;
            $tps_error_control['name_control'] = $controllerName;
            $tps_error_control['dir_control'] = APPS_CONTROL;
            $plantilla->set($tps_error_control);
            header("HTTP/1.0 400");
            echo $plantilla->get();
            exit ;
        }
        //die('El controlador no existe - 404 not found');

        //Si no existe la clase que buscamos y su acción, tiramos un error 404
        if (is_callable(array($controllerName, $actionName)) == false) {
            $plantilla = new plantilla();
            $plantilla->load("no_accion", 38388737400 , 'core/vista/error/');
            $tps_error_control = array();
            $tps_error_control['base_url'] = URL_BASE;
            $tps_error_control['name_control'] = $controllerName;
            $tps_error_control['name_funcion'] = $actionName;
            $tps_error_control['dir_control'] = APPS_CONTROL;
            $plantilla->set($tps_error_control);
            header("HTTP/1.0 400");
            echo $plantilla->get();
            if (($controllerName != 'monitor') && ($actionName != 'bmonitor')) {
                trigger_error($controllerName . '->' . $actionName . '` no existe', E_USER_NOTICE);
            }

            return false;
        }

        //Si todo esta bien, creamos una instancia del controlador y llamamos a
        // la accion
        $controller = new $controllerName();

        if(!isset($param1)){
            $controller->$actionName();
        } elseif (!isset($param2)) {
            $controller->$actionName($param1);
        } elseif (!isset($param3)) {
            $controller->$actionName($param1,$param2);
        } elseif (!isset($param4)) {
            $controller->$actionName($param1,$param2,$param3);
        } else {
            $controller->$actionName($param1,$param2,$param3,$param4);
        }
    }

}
?>