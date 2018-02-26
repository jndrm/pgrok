<?php

namespace Drmer\Pgrok;

use React\Socket\Connector;

abstract class Client
{
    protected $loop = null;

    protected $connector = null;

    protected $connection = null;

    protected $typeName = '';

    public $name = null;

    public function __construct($loop, $options=[])
    {
        $this->loop = $loop;

        $this->name = str_random(8);
        
        $this->connector = new Connector($loop, $options);
    }

    public function connect($uri)
    {
        $this->info("connecting {$uri}");
        $this->connector->connect($uri)->then(function($cli) {
            $this->connection = $cli;
            
            $this->info("connected");

            $cli->on('data', [$this, 'onReceive']);
            $cli->on('close', [$this, 'onClose']);
            $cli->on('error', [$this, 'onError']);

            $cli->once('error', function() {
                $this->connection = null;
            });

            $cli->once('close', function() {
                $this->connection = null;
            });

            // 连接成功
            $this->onConnect();
        }, 'printf');
    }

    public function send($msg)
    {
        $this->connection && $this->connection->write($msg);
    }

    public function isConnected()
    {
        return null != $this->connection;
    }

    public function getConnector()
    {
        return $this->connector;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function info($msg)
    {
        $time = date('Y-m-d H:i:s');
        echo "[$time] [{$this->typeName}:{$this->name}] {$msg}\n";
    }

    public abstract function onConnect();

    public abstract function onReceive($data);

    public abstract function onError();

    public abstract function onClose();
}