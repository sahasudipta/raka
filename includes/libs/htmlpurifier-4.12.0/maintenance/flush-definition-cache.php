#!/usr/bin/php
<?php

chdir(dirname(__FILE__));
require_once 'common.php';
assertCli();

/**
 * @file
 * Flushes the definition serial cache. This file should be
 * called if changes to any subclasses of HTMLPurifier_Definition
 * or related classes (such as HTMLPurifier_HTMLModule) are made. This
 * may also be necessary if you've modified a customized version.
 *
 * @param Accepts one argument, cache type to flush; otherwise flushes all
 *      the caches.
 */

echo "Flushing cache... \n";

require_once(dirname(__FILE__) . '/../library/HTMLPurifier.auto.php');

$config = HTMLPurifier_Config::createDefault();

$names = array('HTML', 'CSS', 'URI', 'Test');
if (isset($argv[1])) {
    if (in_array(filter_var($argv[1],FILTER_SANITIZE_STRING), $names)) {
        $names = array(filter_var($argv[1],FILTER_SANITIZE_STRING));
    } else {
        throw new Exception("Cache parameter {$argv[1]} is not a valid cache");
    }
}

foreach ($names as $name) {
    echo " - Flushing $name\n";
    $cache = new HTMLPurifier_DefinitionCache_Serializer($name);
    $cache->flush($config);
}

echo "Cache flushed successfully.\n";

// vim: et sw=4 sts=4
