<?php

namespace Drmer\Pgrok;

class ProxyClient extends PgrokClient
{
    protected $clientId = null;

    protected $proxyStart = false;

    protected $localClient = null;

    protected $typeName = 'pxy';

    protected $tunnelList = [];

    protected $toClose = false;

    protected $bufferEmpty = false;

    protected $sendLength = 0;

    public function __construct($loop, $config, $clientId)
    {
        parent::__construct($loop, $config);

        $this->clientId = $clientId;

        $this->start();
    }

    public function setTunnels($tunnels)
    {
        $this->tunnelList = $tunnels;
    }

    public function onConnect()
    {
        $this->info("New connection to {$this->host}:{$this->port}");
        $this->sendPack($this->regProxy());
    }

    public function onReceive($data)
    {
         //  已经有本地客户端了，将数据直接转给客户端
        if ($this->localClient) {
            $this->sendLength += strlen($data);
            return;
        }
        // 暂存数据
        $this->recvBuffer .= $data;
        $length = byte_to_length(substr($this->recvBuffer, 0, 8));
        if (strlen($this->recvBuffer) < (8 + $length)) {
            $this->info("Reading message with length: {$length}");
            return;
        }
        // 数据长度足够  读取原始数据
        $raw = substr($this->recvBuffer, 8, $length);
        $json = json_decode($raw);
        $this->info("Read message {$raw}");
        if ($json->Type !== 'StartProxy') {
            return;
        }
        $url = object_get($json, 'Payload.Url');
        $tunnel = $this->findTunnel($url);
        if (!$tunnel) {
            $this->info("No tunnel for {$url} found.");
            return;
        }
        $this->connection->pause();
        $this->localClient = new LocalClient($this->loop, $this, $tunnel);
        $this->localClient->toSend(substr($this->recvBuffer, 8 + $length));
        // edit buffer.
        $this->recvBuffer = null;
        $this->localClient->start();
    }

    public function findTunnel($url)
    {
        foreach ($this->tunnelList as $tunnel) {
            if ($tunnel->url == $url) {
                return $tunnel;
            }
        }
        return null;
    }

    public function onError()
    {
    }

    public function onClose()
    {
        $this->info("Copied {$this->sendLength} bytes before closed");
        $this->localClient = null;
    }

    private function regProxy()
    {
        return [
            'Type' => 'RegProxy',
            'Payload' => [
                'ClientId' => $this->clientId
            ],
        ];
    }
}