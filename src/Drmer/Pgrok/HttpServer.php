<?php

namespace Drmer\Pgrok;

use React\Http\Response as HttpResponse;
use React\Http\Server as Server;
use React\Socket\Server as SocketServer;
use Evenement\EventEmitter;

class HttpServer extends EventEmitter
{
    protected $loop = null;

    public function __construct($loop)
    {
        $server = new Server(function ($request) {
            return new HttpResponse(
                200,
                array('Content-Type' => 'text/plain'),
                "Hello World!\n"
            );
        });

        $server->listen(new SocketServer('0.0.0.0:9080', $loop));

        $this->info("Server running at http://127.0.0.1:9080");
    }

    public function info($msg)
    {
        echo "{$msg}\n";
    }
}