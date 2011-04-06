<?php

require_once('Zend/Cache.php');

// front end options, cache for 1 minute
$frontendOptions = array(
   'lifetime' => null
);

// backend options
$backendOptions = array(
    'cache_dir' => 'cache' // Directory where to put the cache files
);

// cache
$cache = Zend_Cache::factory('Core','File',$frontendOptions,$backendOptions);

?>
