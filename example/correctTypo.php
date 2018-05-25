<?php
/**
 * Example script of using etersoft/typos_client package
 */

require __DIR__ . '/../vendor/autoload.php';

use My\MyClientInterface;

$interface = new MyClientInterface();

$client = new \Etersoft\Typos\TyposClient($interface);

echo $client->run();