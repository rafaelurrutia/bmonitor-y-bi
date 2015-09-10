<?php 
 $_SESSION['id_server']=100;
 include_once "fast/phpfastcache/phpfastcache.php";
  phpFastCache::setup("storage","files");
 phpFastCache::setup("path", "/tmp");

 $cache = phpFastCache();
//$cache->clean();

  $key="100";


$obj = $cache->get($key);
 if ($obj == null) {
  echo "nulo";

  $cache->set($key, array('clock'=>0,'value'=>0,'valid'=>0,'t'=>''),60);
}
else {
  echo "desde cache";
  var_dump($obj);
}

?>

  
