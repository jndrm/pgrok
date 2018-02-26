<?php

use Drmer\Pgrok\ControlClient;
use React\EventLoop\Factory as LoopFactory;

require_once __DIR__ . "/vendor/autoload.php";

$config = require_once __DIR__ . "/config/pgrok.php";

echo "Pgrok starting\n";

$options = [
    'host'       => $config['host'],
    'port'       => $config['port'],
    'token'      => $config['token'],
    'verify_ssl' => $config['verify_ssl'],
];

$loop = LoopFactory::create();

new ControlClient($loop, $options, $config['tunnels']);

$loop->run();