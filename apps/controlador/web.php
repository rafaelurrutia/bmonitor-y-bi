<?php

class Web extends Control
{
    public function speedtest()
    {
        if(isset($_GET['idServer'])) {
           $_SESSION['OOKLAIDSERVER'] = $_GET['idServer']; 
        } else {
           $_SESSION['OOKLAIDSERVER'] = '';
        }
 
        if(isset($_GET['lat'])) {
           $_SESSION['OOKLALAT'] = $_GET['lat']; 
        } else {
           unset($_SESSION['OOKLALAT']);
        }
 
        if(isset($_GET['lon'])) {
           $_SESSION['OOKLALON'] = $_GET['lon']; 
        } else {
           unset($_SESSION['OOKLALON']);
        }
                      
        $this->plantilla->load("web/speedtest.net");
        
        echo $this->plantilla->get();
    }

    public function speedtest2()
    {
        if(isset($_GET['idServer'])) {
           $_SESSION['OOKLAIDSERVER'] = $_GET['idServer']; 
        } else {
           $_SESSION['OOKLAIDSERVER'] = '';
        }
 
        if(isset($_GET['lat'])) {
           $_SESSION['OOKLALAT'] = $_GET['lat']; 
        } else {
           unset($_SESSION['OOKLALAT']);
        }
 
        if(isset($_GET['lon'])) {
           $_SESSION['OOKLALON'] = $_GET['lon']; 
        } else {
           unset($_SESSION['OOKLALON']);
        }
                      
        $this->plantilla->load("web/velocidad.vtr");
        
        echo $this->plantilla->get();
    }
	
    public function youtube3($idVideo = 'YE7VzlLtp-4',$quality = 'default', $html5 = false)
    {
        if($html5 == false) {
            $this->plantilla->load("web/video2"); 
        } else {
            $this->plantilla->load("web/video");
        }

        $vars['VIDEOID'] = $idVideo;
        $vars['QUALITY'] = $quality;
        
        if($html5 === false) {
            $vars['DOCTYPE2'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
            $vars['HTML5'] = '0';
            $vars['META'] = '<meta http-equiv="X-UA-Compatible" content="IE=7" >';
        } else {
            $vars['DOCTYPE2'] = '<!DOCTYPE html>';
            $vars['HTML5'] = '1';
            $vars['META'] = '<meta http-equiv="X-UA-Compatible" content="IE=10" >';
        }
       
        $this->plantilla->set($vars);
       // setcookie("stnetsid", 'p6jrtk0i9q5eb3cgc96nap8ie4',time()+3600,'/','bmonitor.baking.cl');
        echo $this->plantilla->get();
    }
	
	 public function youtube2($idVideo = 'YE7VzlLtp-4',$quality = 'default', $html5 = false){
		$this->youtube($idVideo,$quality,$html5);
	 }

    public function youtube($idVideo = 'YE7VzlLtp-4',$quality = 'default', $html5 = false)
    {
        $this->plantilla->load("web/video3", false);

        $vars['VIDEOID'] = $idVideo;
        $vars['QUALITY'] = $quality;
        
        if($html5 === false) {
            $vars['DOCTYPE'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
            $vars['HTML5'] = '0';
            $vars['META'] = '<meta http-equiv="X-UA-Compatible" content="IE=7" >';
        } else {
            $vars['DOCTYPE'] = '<!DOCTYPE html>';
            $vars['HTML5'] = '1';
            $vars['META'] = '<meta http-equiv="X-UA-Compatible" content="IE=10" >';
        }
       
        $this->plantilla->set($vars);
        $this->plantilla->finalize();
    }
	       
    public function gtd()
    {
        $this->plantilla->load("web/nacional.grupogtd.com");
        echo $this->plantilla->get();
    }
    
    public function speedtestAdFlash()
    {
        $param = $_GET;
    }

    public function speedtestConfig($result,$url=false,$php=false,$cache = true)
    {
        if($url == false) {
           $url = 'http://www.speedtest.net/speedtest-config.php'; 
        } else {
           $url = 'http://'.$url.'/'.$php;
        }

        $cache_dir = site_path . 'cache/';

        $cache_time = 0;

        $file = $cache_dir . "cache_" . md5(serialize($url)) . "_ookla2.cache";

        if (file_exists($file) && (fileatime($file) + $cache_time) > time() && $cache == true) {
            $config = file_get_contents($file);       
        } else {
            
            $ch = curl_init($url."?x=" . $_GET['x']);
            curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            $config = curl_exec($ch);
            curl_close($ch);
    
            $fp = fopen($file, 'w');
            fwrite($fp, $config);
            fclose($fp);
        }
        $data = new SimpleXMLElement($config);
        $serverConfig = "server-config";
        
        if(isset($_SESSION['OOKLAIDSERVER'])) {
           $data->$serverConfig->attributes()->preferredserverid = $_SESSION['OOKLAIDSERVER'];
        }

        if(isset($_SESSION['OOKLALON'])) {
          $data->client->attributes()->lon = $_SESSION['OOKLALON'];
        }
        
        if(isset($_SESSION['OOKLALAT'])) {
           $data->client->attributes()->lat = $_SESSION['OOKLALAT'];
        }
        
        $data->client->attributes()->isp = "HuuuuuHAAAAAAA";
               
        $data->$serverConfig->attributes()->threadcount = 4;
       // $data->client->attributes()->lat="39.0437";
       // $data->client->attributes()->lat="-77.4875";
        $data->download->attributes()->threadsperurl = 4;
        $data->panels->attributes()->panel0 = 'wave-wide';
        $data->panels->attributes()->panel1 = 'share-wide';
        $data->panels->attributes()->panel2 = 'link:/results.php?source=compare';
        $reporting = $data->addChild('reporting');
		$reporting->addAttribute("jsreporting","2");
		$reporting->addAttribute("jscalls","3");
        header("Content-type: text/xml; charset=utf-8");
        echo $data->asXML();
        exit;

    }
    
    public function speedtestServer($result,$cache = true)
    {
        $cache_dir = site_path . 'cache/';

        $cache_time = 0;

        $file = $cache_dir . "cache_" . md5(serialize('http://c.speedtest.net/speedtest-servers-static.php?threads=2')) . "_ookla2.cache";

        if (file_exists($file) && (fileatime($file) + $cache_time) > time() && $cache == true) {
            $config = file_get_contents($file);
        } else {      
            $ch = curl_init("http://c.speedtest.net/speedtest-servers-static.php?threads=2");
            curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            $config = curl_exec($ch);
            curl_close($ch);
    
            $fp = fopen($file, 'w');
            fwrite($fp, $config);
            fclose($fp);
        }
        header("Content-type: text/xml; charset=utf-8");
        echo $config;
        exit;

    }

    public function ooklaServer($cache = true)
    {
        $cache_dir = site_path . 'cache/';

        $cache_time = 0;

        $file = $cache_dir . "cache_" . md5(serialize('http://c.speedtest.net/speedtest-servers-static.php?threads=2')) . "_ookla2.cache";

        if (file_exists($file) && (fileatime($file) + $cache_time) > time() && $cache == true) {
            $config = file_get_contents($file);
        } else {      
            $ch = curl_init("http://c.speedtest.net/speedtest-servers-static.php?threads=2");
            curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            $config = curl_exec($ch);
            curl_close($ch);
    
            $fp = fopen($file, 'w');
            fwrite($fp, $config);
            fclose($fp);
        }
        
        $data = new SimpleXMLElement($config);
        $table = '';
        foreach ($data->servers->server as $key => $value) {
            $table .= "<tr>
                <td>".$value->attributes()->id."</td>
              <td>".$value->attributes()->url."</td>
              <td>".$value->attributes()->lat."</td>
              <td>".$value->attributes()->lon."</td>
              <td>".$value->attributes()->name."</td>
              <td>".$value->attributes()->country."</td>
              <td>".$value->attributes()->cc."</td>
              <td>".$value->attributes()->sponsor."</td>
            </tr>";
        }

        $this->plantilla->load("web/ooklaServer");

        $section = new Section( );
        $vars['header'] = $section->header();
        $vars['footer'] = $section->footer();
        $vars['dataTable'] =$table;
        $this->plantilla->set($vars);
        $this->plantilla->finalize();
    }
    
    public function getconfig($result)
    {

        $this->curl->cargar("http://www.speedtest.net/speedtest-config.php", 1);

        $this->curl->ejecutar();

        $config = $this->curl->getContent(1);

        echo $config;
        exit ;

        $cache_dir = site_path . 'cache/';

        $cache_time = 1200;

        $file = $cache_dir . "cache_" . md5(serialize('http://www.speedtest.net/speedtest-config.php')) . "_ukla.cache";

        if (file_exists($file) && (fileatime($file) + $cache_time) > time()) {
            $config = file_get_contents($file);
        } else {
            $this->curl->cargar("http://www.speedtest.net/speedtest-config.php", 1);

            $this->curl->ejecutar();

            $config = $this->curl->getContent(1);

            $fp = fopen($file, 'w');
            fwrite($fp, $config);
            fclose($fp);
        }

        $configArray = simplexml_load_string($config);

        //Config clientes
        unset($config);

        foreach ($configArray->client->attributes() as $key => $value) {
            $config['client'][$key] = (string)$value[0];
        }

        foreach ($configArray->times->attributes() as $key => $value) {
            $config['times'][$key] = (string)$value[0];
        }

        foreach ($configArray->download->attributes() as $key => $value) {
            $config['download'][$key] = (string)$value[0];
        }

        foreach ($configArray->upload->attributes() as $key => $value) {
            $config['upload'][$key] = (string)$value[0];
        }

        //Cargando Cache

        $file = $cache_dir . "cache_" . md5(serialize('http://speedtest.net/speedtest-servers.php')) . "_ukla.cache";

        if (file_exists($file) && (fileatime($file) + $cache_time) > time()) {
            $serverXml = file_get_contents($file);
        } else {
            $this->curl->cargar("http://speedtest.net/speedtest-servers.php", 1);

            $this->curl->ejecutar();

            $serverXml = $this->curl->getContent(1);

            $fp = fopen($file, 'w');
            fwrite($fp, $serverXml);
            fclose($fp);
        }

        $serverArray = simplexml_load_string($serverXml);

        $servers = array();
        $count = 0;
        foreach ($serverArray->servers->server as $key => $server) {
            $attr = $server->attributes();
            //$d = $this->distance(array((float)$config['client']['lat'], (float)$config['client']['lon']),
            // array((float)$attr->lat, (float)$attr->lon));
            $d = $this->distanceGeoPoints((float)$config['client']['lat'], (float)$config['client']['lon'], (float)$attr->lat, (float)$attr->lon);
            $servers[$d . $count] = $attr;
            $count++;
        }

        ksort($servers);

        $count = 0;
        foreach ($servers as $key => $value) {

            $serversValid[$count]['url'] = (string)$value->url;
            $serversValid[$count]['lat'] = (string)$value->lat;
            $serversValid[$count]['lon'] = (string)$value->lon;
            $serversValid[$count]['name'] = (string)$value->name;
            $serversValid[$count]['country'] = (string)$value->country;
            $serversValid[$count]['cc'] = (string)$value->cc;
            $serversValid[$count]['sponsor'] = (string)$value->sponsor;
            $serversValid[$count]['id'] = (string)$value->id;
            $serversValid[$count]['dist'] = (string)$key;

            if ($count == 4) {
                break;
            }

            $count++;
        }

        var_dump($serversValid);

    }

    public function distanceGeoPoints($lat1, $lng1, $lat2, $lng2)
    {

        $earthRadius = 6371.75;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $dist = $earthRadius * $c;

        // from miles
        $meterConversion = 1609;
        $geopointDistance = $dist * $meterConversion;

        return $geopointDistance;
    }

    public function distance($origin, $destination)
    {
        list($lat1, $lon1) = $origin;
        list($lat2, $lon2) = $destination;

        $radius = 6371;

        $dlat = deg2rad($lat2 - $lat1);
        $dlon = deg2rad($lon2 - $lon1);

        $a = (sin($dlat / 2) * sin($dlat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dlon / 2) * sin($dlon / 2));

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $d = $radius * $c;

        return $d;
    }

}
?>