<?php
class Threader
{

    var $threadName = null;
    var $rid = null;
    var $error = null;
    var $pipes = array();
    var $active = false;

    public static function getInstance($cmd, $arrar_var)
    {
        $pipes = array();
        for ($i = 'A'; $i != 'AA'; $i++) {
            $pipes[] = $i;
        }
        $pipes_active = array();
        foreach ($arrar_var as $key => $value) {
            ${$pipes[$key]} = new self($cmd, $value, $pipes[$key]);
        }
        return true;
    }

    function Threader($cmd = null, $vars = null, $name = null)
    {
        //Validando Comando

        $php_cmd = exec('whereis php');
        $php_cmd = explode(' ', $php_cmd);
        if (isset($php_cmd[1])) {
            $php_cmd = $php_cmd[1];
        } else {
            $php_cmd = "/usr/bin/php";
        }

        $descriptorspec = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("pipe", "w"));
        $pipes = array();
        if (!empty($cmd)) :$this -> threadName = $name;
            try {
                // @formatter:off
                $this -> rid = proc_open("$php_cmd -f $cmd $vars", $descriptorspec, $this -> pipes, SITE_PATH 
                                           . 'server', $_ENV);
                // @formatter:on
                $this -> active = true;
            } catch (exception $e) {
                $this -> active = false;
                $this -> error = $e -> getMessage();
            }
        endif;
    }

    public function listen()
    {
        if (is_resource($this -> rid) && !empty($this -> pipes)) {
            $stdout = (isset($this -> pipes['1'])) ? $this -> pipes['1'] : null;
            return fgets($stdout);
        } else {
            return null;
        }
    }

    function __destruct()
    {
        $this -> active = false;
        if (is_resource($this -> rid)) {
            proc_close($this -> rid);
        }
        if (!empty($this -> error)) {
            $this -> error("Error:", $this -> error, 'logs_thread');
            echo $this -> error;
        }
    }

}
?>