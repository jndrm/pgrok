<?php

namespace Drmer\Pgrok;

abstract class PgrokClient extends Client
{
    protected $clientId = null;

    protected $recvBuffer = '';

    protected $host = null;

    protected $port = 4443;

    public function __construct($loop, $config)
    {
        $this->host = $config['host'];
        $this->port = $config['port'];
        parent::__construct($loop, [
            'tls' => [
                'allow_self_signed' => true,
            ],
        ]);
    }

    public function start()
    {
        $this->connect("tls://{$this->host}:{$this->port}");
    }
    
    public function sendPack($msg)
    {
        $msg = is_string($msg) ? $msg : json_encode($msg);
        $this->info("send >>>> {$msg}");
        $this->send(length_to_byte(strlen($msg)) . $msg);
    }
}