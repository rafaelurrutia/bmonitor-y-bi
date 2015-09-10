<?php
if (!class_exists("Language")) {
    class Language
    {

        private static $instance;

        public $language = '';
        private $tags = array();

        public static function getInstance()
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
           
            if (isset($_COOKIE['language'])) {
                $this->language = $_COOKIE['language'];
                $codeLang = 0;
            } elseif (isset($_SESSION['language'])) {
                $this->language = $_SESSION['language'];
                $codeLang = 1;
            } else {
                $this->language = 'en';
                $codeLang = 2;
            }

            $site_path = realpath(dirname(__FILE__) . '/../../') . "/";

            $this->tags = include $site_path . "apps/language/" . $this->language . '.php';
        }

        public function __destruct()
        {
            $this->language = '';
            $this->tags = array();
        }

        public function __get($tag)
        {
            return $this->tags[$tag];
        }

    }

}
?>