<?php
class EnhancedCss
{
    private $values;
    private $cssFile;

    public function __construct($cssFile)
    {

        define('DIRSEP', DIRECTORY_SEPARATOR);

        $this -> site_path = realpath(dirname(__FILE__) . DIRSEP . '..' . DIRSEP) . DIRSEP;

        $cssFile = $this -> site_path . "css/" . $cssFile . ".css";

        if (!file_exists($cssFile)) {
            header('HTTP/1.0 404 Not Found');
            exit ;
        }

        $modified = filemtime($cssFile);
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $modified) {
                header('HTTP/1.1 304 Not Modified');
                exit();
            }
        }
        header('Last-Modified: ' . gmdate("D, d M Y H:i:s", $modified) . ' GMT');

        $this -> cssFile = $cssFile;
    }

    private function parse()
    {
        if (!$content = $this -> cache()) {
            $lines = file($this -> cssFile);
            foreach ($lines as $line) {
                $content .= $this -> findAndReplaceVars($line);
            }
        }
        return $content;
    }

    private function cache($content = false)
    {
        $cacheFile = $this -> site_path . '../cache/' . urlencode($this -> cssFile);
        if (file_exists($cacheFile) && filemtime($cacheFile) > filemtime($this -> cssFile)) {
            return file_get_contents($cacheFile);
        } else if ($content) {
            file_put_contents($cacheFile, $content);
        }
        return $content;
    }

    private function findAndReplaceVars($line)
    {
        preg_match_all('/\s*\\$([A-Za-z1-9_\-]+)(\s*:\s*(.*?);)?\s*/', $line, $vars);

        $found = $vars[0];
        $varNames = $vars[1];
        $varValues = $vars[3];
        $count = count($found);

        for ($i = 0; $i < $count; $i++) {
            $varName = trim($varNames[$i]);
            $varValue = trim($varValues[$i]);
            if ($varValue) {
                $this -> values[$varName] = $this -> findAndReplaceVars($varValue);
            } else if (isset($this -> values[$varName])) {
                $line = preg_replace('/\\$' . $varName . '(\W|\z)/', $this -> values[$varName] . '\\1', $line);
            }
        }
        $line = str_replace($found, '', $line);
        return $line;
    }

    public function display()
    {
        header('Content-type: text/css');
        echo $this -> cache($this -> parse());
    }

}

if (isset($_GET['css'])) {
    $css = new EnhancedCss($_GET['css']);
    $css -> display();
}
?>