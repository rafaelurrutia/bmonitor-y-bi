<?php
if (!class_exists("plantilla")) {
    class plantilla
    {

        private $cache_dir;
        private $cache_time;
        private $caching = false;
        private $cleaning = false;

        private $file = '';
        protected $tpl_file, $vars;
        public $_this;
        private $language = 'en';

        private $memcacheHost = 'localhost';
        private $memcachePort = '11211';
        private $memcacheNameSpace = 'duperCache';

        var $option = array(
            "path" => 'cache', // path for cache folder
            "htaccess" => true, // auto create htaccess
            "securityKey" => 'donePerroMuerde$$MarcaQueda', // Key Folder, Setup Per Domain will good.
            "storage" => 'files',
            "memcached" => array(0 => array(
                    "host" => '127.0.0.1',
                    'port' => '11211',
                    'nameSpace' => 'duperCache'
                ))
        );

        public static $driver;

        public function __construct($lang = '')
        {
            if(strlen($lang) > 1){
                $this->language = $lang; 
            }
            $this->htaccessGen();
        }

        public function load($template = false, $cache = false, $dir = 'sitio/vista/')
        {
            if (!$template || $template == '') {
                $this->tpl_file = false;
                return false;
            }

            if ($this->caching === true && (is_array($cache) || $cache !== false)) {

                if (is_array($cache)) {
                    if (!isset($cache['time']) || !is_numeric($cache['time'])) {
                        $cache['time'] = 36000;
                    }
                    if (!isset($cache['code'])) {
                        $cache['code'] = '';
                    }
                    $this->setCache($template . $cache['code'], $cache['time']);
                } else {
                    if (!is_numeric($cache)) {
                        $cache = 36000;
                    }
                    $this->setCache($template, $cache);
                }
            } else {
                $this->caching = false;
            }
            
            $this->vars = "";
            $this->tpl_file = site_path . $dir . $template . '.php';
        }

        public function loadSec($template, $valida, $cache = false, $template_denied = "basic/denegado", $dir = 'sitio/vista/')
        {

            if (is_array($valida)) {

                if ($valida->redirect == true) {
                    $template = "basic/expire";
                } elseif ($valida->access == false) {
                    $template = $template_denied;
                }

            } else {
                if (!$valida) {
                    $template = $template_denied;
                }
            }

            if ($this->caching === true && (is_array($cache) || $cache !== false)) {

                if (is_array($cache)) {
                    if (!isset($cache['time']) || !is_numeric($cache['time'])) {
                        $cache['time'] = 36000;
                    }
                    if (!isset($cache['code'])) {
                        $cache['code'] = '';
                    }
                    $this->setCache($template . $cache['code'], $cache['time']);
                } else {
                    if (!is_numeric($cache)) {
                        $cache = 36000;
                    }
                    $this->setCache($template, $cache);
                }
            } else {
                $this->caching = false;
            }

            $this->vars = "";
            $this->tpl_file = site_path . $dir . $template . '.php';
        }

        public function set($vars)
        {
            $this->vars = (empty($this->vars)) ? $vars : $this->vars . $vars;
        }

        public function get()
        {
            if (!$this->tpl_file || ($this->tpl_file === '') || !($this->fd = fopen($this->tpl_file, 'r'))) {
                $error = '<div id="loginResult" class="notification error png_bg">';
                $error .= "<div id='message'>Error al abrir la plantilla, $this->tpl_file</div></div>";
                echo $error;
            } else {

                $this->template_file = fread($this->fd, filesize($this->tpl_file));
                fclose($this->fd);
                
                $this->mihtml = $this->template_file;
                $this->mihtml = str_replace("'", "\'", $this->mihtml);
                $this->mihtml = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", $this->mihtml);

                $this->vars['url_base'] = URL_BASE;
                $this->vars['url_base_full'] = URL_BASE_FULL;
                
               
                if (isset($_SESSION['name'])) {
                    $this->vars['name_user'] = $_SESSION['name'];
                }

                if (isset($_SESSION['theme'])) {
                    $this->vars['name_user'] = $_SESSION['name'];
                }
                
                if (isset($_SESSION['language']) && strlen($_SESSION['language']) > 1) {              
                    $lang = $_SESSION['language'];
                } else {
                    $lang = $this->language;                    
                }
                
                $this->vars['language'] = $lang;
                
                $tags = include site_path . "apps/language/" . $lang . '.php';
                
                $this->vars = array_merge($this->vars,$tags);

                if (!empty($this->vars)) {
                    
                    reset($this->vars);
                    while (list($key, $val) = each($this->vars)) {
                        $$key = $val;
                    }
                    @eval("\$this->mihtml = '$this->mihtml';");
                    if (!empty($this->vars)) {
                        reset($this->vars);
                    }
                    while (list($key, $val) = each($this->vars)) {
                        unset($$key);
                    }
                }

                $this->mihtml = str_replace("\'", "'", $this->mihtml);
                return $this->mihtml;
            }
        }

        private function buildKey($key) {
                return '{'.$key.'}';
        }
        
        public function get2()
        {
            if (!$this->tpl_file || ($this->tpl_file === '') || !($this->fd = fopen($this->tpl_file, 'r'))) {
                $error = '<div id="loginResult" class="notification error png_bg">';
                $error .= "<div id='message'>Error al abrir la plantilla, $this->tpl_file</div></div>";
                echo $error;
            } else {
                $this->template_file = fread($this->fd, filesize($this->tpl_file));
                fclose($this->fd);

                $this->vars['url_base'] = URL_BASE;
                $this->vars['url_base_full'] = URL_BASE_FULL;
                
                
                if (isset($_SESSION['name'])) {
                    $this->vars['name_user'] = $_SESSION['name'];
                }

                if (isset($_SESSION['theme'])) {
                    $this->vars['name_user'] = $_SESSION['name'];
                }
                
                if (isset($_SESSION['language']) && strlen($_SESSION['language']) > 1) {              
                    $lang = $_SESSION['language'];
                } else {
                    $lang = $this->language;                    
                }
                
                $this->vars['language'] = $lang;
                
                $tags = include site_path . "apps/language/" . $lang . '.php';
                
                $this->vars = array_merge($this->vars,$tags);
                reset($this->vars);            
                $keys=array_map(array('plantilla','buildKey'), array_keys($this->vars));
                
                return str_replace( $keys, array_values($this->vars), $this->template_file);
            }
        }
        
        public function getOne($template, $param = array(), $dir = 'sitio/vista/')
        {

            $tplFile = site_path . $dir . $template . '.php';

            if (!$tplFile || ($tplFile === '') || !($fd = fopen($tplFile, 'r'))) {
                $error = '<div id="loginResult" class="notification error png_bg">';
                $error .= "<div id='message'>Error al abrir la plantilla, $this->tpl_file</div></div>";
                return $error;
            } else {

                $templateFile = fread($fd, filesize($tplFile));
                fclose($fd);
                $mihtml = $templateFile;
                $mihtml = str_replace("'", "\'", $mihtml);
                $mihtml = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", $mihtml);

                $varsSystem['url_base'] = URL_BASE;
                $varsSystem['url_base_full'] = URL_BASE_FULL;

                if (isset($_SESSION['name'])) {
                    $varsSystem['name_user'] = $_SESSION['name'];
                }

                if (isset($_SESSION['theme'])) {
                    $varsSystem['name_user'] = $_SESSION['name'];
                }

                if (isset($_SESSION['language'])) {
                    $lang = $_SESSION['language'];
                } else {
                    $lang = $this->language;                    
                }

                $varsSystem['language'] = $lang;
                
                $tags = include site_path . "apps/language/" . $lang . '.php';
                
                $varsSystem = array_merge($varsSystem,$tags);

                $vars = array_merge($varsSystem,$param);
                
                if (!empty($vars)) {

                    reset($vars);
                    while (list($key, $val) = each($vars)) {
                        $$key = $val;
                    }

                    @eval("\$mihtml = '$mihtml';");
                    if (!empty($vars)) {
                        reset($vars);
                    }
                    while (list($key, $val) = each($vars)) {
                        unset($$key);
                    }
                }

                $mihtml = str_replace("\'", "'", $mihtml);
                return $mihtml;
            }
        }

        public function finalize()
        {
            echo $this->get();
            if($this->caching === true) {
                $this->cacheClose();
            }
            exit;
        }

        public function setCache($sql, $time = 600)
        {
            if (isset($_COOKIE['language'])) {
                $language = $_COOKIE['language'];
            } elseif (isset($_SESSION['language'])) {
                $language = $_SESSION['language'];
            } else {
                $language = $this->language;
            }

            if (isset($_COOKIE['id_group'])) {
                $idGroup = $_COOKIE['id_group'];
            } elseif (isset($_SESSION['id_group'])) {
                $idGroup = $_SESSION['id_group'];
            } else {
                $idGroup = 'none';
            }

            $this->cache_time = $time;

            $this->file = md5($sql) . "_" . $language . "_" . $idGroup;

            $cache = $this->cacheIsExisting($this->file, $time);

            if ($cache !== false) {
                echo $this->cacheGet($this->file, $time);
                exit ;
            } else {
                ob_start();
            }

        }

        public function cacheJSON($sql, $time = 120)
        {
            return $this->setCache($sql, $time);
        }

        public function cacheClose()
        {
            if ($this->caching !== false) {
                $data = ob_get_clean();
                echo $data;
                $this->cacheSet($this->file, $data, $this->cache_time);
            }
        }

        public function cacheDeleteAll()
        {
            $result = array_map('unlink', glob(SITE_PATH . "cache/*.cache"));
        }

        /*
         * CACHE ********************  "2.0" *****************
         */

        private function getPath()
        {
            if (isset($this->option["path"]) && $this->option["path"] != '') {
                $dirCache = site_path . $this->option["path"] . "/";
            } else {
                $dirCache = site_path . "cache/";
            }
            return $dirCache;
        }

        /*
         * Check Driver active
         */

        private function checkdriver()
        {
            $dirCache = site_path . $this->option["path"];

            $drivers = array(
                "apc",
                "sqlite",
                "files",
                "memcached"
            );

            if ($this->option["storage"] !== 'auto' && (in_array($this->option["storage"], $drivers))) {
                return $this->option["storage"];
            }

            if (extension_loaded('apc') && ini_get('apc.enabled') && strpos(PHP_SAPI, "CGI") === false) {
                $driver = "apc";
            } elseif (extension_loaded('pdo_sqliteBOR') && is_writeable($this->getPath())) {
                $driver = "sqlite";
            } elseif (is_writeable($this->getPath())) {
                $driver = "files";
            } else if (class_exists("memcached")) {
                $this->instant = new Memcached();
                $driver = "memcached";
            } else {
                return false;
            }

            return $driver;
        }

        /*
         * Auto Create .htaccess to protect cache folder
         */

        private function htaccessGen()
        {
            $dirCache = $this->getPath();
            if ($this->option["htaccess"] == true) {
                if (!file_exists($dirCache . ".htaccess")) {
                    $html = "deny from all\r\nallow from 127.0.0.1";
                    $f = @fopen($dirCache . ".htaccess", "w+");
                    if (!$f) {
                        throw new Exception("Can't create .htaccess", 97);
                    }
                    fwrite($f, $html);
                    fclose($f);
                }
            }

        }

        public function cacheGet($keyword, $option = array())
        {
            $driver = $this->checkdriver();

            $function = $driver . "DriverGet";

            return $this->$function($keyword, $option);
        }

        public function cacheSet($keyword, $value = "", $time = 300, $option = array())
        {
            $driver = $this->checkdriver();

            $function = $driver . "DriverSet";

            return $this->$function($keyword, $value, $time, $option);
        }

        public function cacheIsExisting($keyword, $time = 300)
        {
            $driver = $this->checkdriver();

            $function = $driver . "DriverIsExisting";

            return $this->$function($keyword, $time);
        }

        public function cacheDelete()
        {
            $driver = $this->checkdriver();

            $function = $driver . "DriverDelete";

            return $this->$function();
        }

        public function cacheClean($option = array())
        {
            $driver = $this->checkdriver();

            $function = $driver . "DriverClean";

            return $this->$function($option);
        }

        /*
         *
         * START DRIVE APC
         *
         */
        private function apcDriverGet($keyword, $option = array())
        {
            $data = apc_fetch($keyword, $success);
            if ($success === false) {
                return false;
            }
            return $data;
        }

        private function apcDriverSet($keyword, $value = "", $time = 300, $option = array())
        {
            if (isset($option['skipExisting']) && $option['skipExisting'] == true) {

                return apc_add($keyword, $value, $time);
            } else {
                return apc_store($keyword, $value, $time);
            }
        }

        private function apcDriverDelete($keyword, $option = array())
        {
            return apc_delete($keyword);
        }

        private function apcDriverStats($option = array())
        {
            $res = array(
                "info" => "",
                "size" => "",
                "data" => "",
            );

            try {
                $res['data'] = apc_cache_info("user");
            } catch(Exception $e) {
                $res['data'] = array();
            }

            return $res;
        }

        private function apcDriverClean($option = array())
        {
            @apc_clear_cache();
            @apc_clear_cache("user");
        }

        private function apcDriverIsExisting($keyword, $time = 300)
        {
            if (apc_exists($keyword)) {
                return true;
            } else {
                return false;
            }
        }

        /*
         *
         * START DRIVE FILE
         */

        private function filesDriverSet($keyword, $value = "", $time = 300, $option = array())
        {
            $file = $this->getPath() . "DR_" . $keyword . ".cache";
          
            if (file_exists($file)) {
                unlink($file);
            }

            $fp = fopen($file, 'w');
            fwrite($fp, $value);
            fclose($fp);
        }

        private function filesDriverGet($keyword, $time = 300, $option = array())
        {
            $file = $this->getPath() . "DR_" .$keyword. ".cache";

            if (file_exists($file) && ($time === 0)) {
                return file_get_contents($file);
            } elseif (file_exists($file) && (fileatime($file) + $time) > time()) {
                return file_get_contents($file);
            } else {
                return false;
            }
        }

        private function filesDriverIsExisting($keyword, $time = 300)
        {
            $file = $this->getPath() . "DR_" . $keyword . ".cache";
            if (file_exists($file) && ((int)$time == 0)) {
                return true;
            } elseif (file_exists($file) && (fileatime($file) + $time) > time()) {
                return true;
            } else {
                return false;
            }
        }

        private function filesDriverClean()
        {
            $result = array_map('unlink', glob(SITE_PATH . "cache/*.cache"));
        }

        /*
         *
         * START DRIVE MEMCACHED
         *
         */

        function memcachedDriverConnectServer()
        {
            $s = $this->option['memcached'];
            if (count($s) < 1) {
                $s = array(0 => array(
                        "host" => '127.0.0.1',
                        'port' => '11211',
                        'nameSpace' => 'duperCache',
                        'sharing' => 0
                    ));
            }

            foreach ($s as $server) {
                $name = isset($server['host']) ? $server['host'] : "127.0.0.1";
                $port = isset($server['port']) ? $server['port'] : 11211;
                $sharing = isset($server['sharing']) ? $server['sharing'] : 0;
                $checked = $name . "_" . $port;
                if (!isset($this->checked[$checked])) {
                    if ($sharing > 0) {
                        $this->instant->addServer($name, $port, true, $sharing);
                    } else {
                        $this->instant->addServer($name, $port, true);
                    }
                    $this->checked[$checked] = 1;
                }
            }
        }

        function memcachedDriverSet($keyword, $value = "", $time = 300, $option = array())
        {
            $this->connectServer();
            if (isset($option['isExisting']) && $option['isExisting'] == true) {
                return $this->instant->add($keyword, $value, time() + $time);
            } else {
                return $this->instant->set($keyword, $value, time() + $time);

            }
        }

        function memcachedDriverGet($keyword, $option = array())
        {
            $this->connectServer();
            $x = $this->instant->get($keyword);
            if ($x == false) {
                return false;
            } else {
                return $x;
            }
        }

        function memcachedDriverDelete($keyword, $option = array())
        {
            $this->connectServer();
            $this->instant->delete($keyword);
        }

        function memcachedDriverStats($option = array())
        {
            $this->connectServer();
            $res = array(
                "info" => "",
                "size" => "",
                "data" => $this->instant->getStats(),
            );

            return $res;
        }

        function memcachedDriverClean($option = array())
        {
            $this->connectServer();
            $this->instant->flush();
        }

        function memcachedDriverIsExisting($keyword)
        {
            $this->connectServer();
            $x = $this->instant->get($keyword);
            if ($x == false) {
                return false;
            } else {
                return $x;
            }
        }

    }

}
?>