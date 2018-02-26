<?php

namespace Drmer\Pgrok;

class ControlClient extends PgrokClient
{
    protected $timerId = null;

    protected $clientId = '';

    protected $config = [];

    protected $tunnelList = [];

    protected $authToken = null;

    protected $typeName = 'ctl';

    public function __construct($loop, $config, $tunnels)
    {
        $this->config = $config;
        $this->authToken = $config['token'];
        foreach ($tunnels as $t) {
            $this->tunnelList[] = new Tunnel($t);
        }
        parent::__construct($loop, $config);

        $this->start();
    }

    public function onConnect()
    {
        // 客户端已经连上服务器了
        // 发送认证请求
        $this->sendPack($this->auth());
    }

    public function onReceive($data)
    {
        if (strlen($data) <= 0) {
            return;
        }
        $this->recvBuffer .= $data;

        $length = byte_to_length(substr($this->recvBuffer, 0, 8));
        do {
            if (strlen($this->recvBuffer) < (8 + $length)) {
                $this->info("Reading message with length: {$length}");
                return;
            }
            $raw = substr($this->recvBuffer, 8, $length);
            // edit buffer
            $this->recvBuffer = substr($this->recvBuffer, 8 + $length);

            $this->handleData($raw);

            if (strlen($this->recvBuffer) < 8) {
                return;
            }

            $length = byte_to_length(substr($this->recvBuffer, 0, 8));
        } while(strlen($this->recvBuffer) >= (8 + $length));
    }

    public function handleData($raw)
    {
        $this->info("recv <<<< {$raw}");
        $json = json_decode($raw);
        $payload = $json->Payload;
        switch ($json->Type) {
            case 'AuthResp':
                $this->clientId = $payload->ClientId;
                // register tunnels
                $this->startTimer();
                $this->registerTunnels();
                break;
            case 'ReqProxy':
                // new proxy. 
                with(new ProxyClient($this->loop, $this->config, $this->clientId))->setTunnels($this->tunnelList);
                break;
            case 'NewTunnel':
                if (null != $payload->Error) {
                    $this->error("Add tunnel failed, {$payload->Error}");
                } else {
                    foreach ($this->tunnelList as $tunnel) {
                        if ($tunnel->requestId == $payload->ReqId) {
                            $tunnel->url = $payload->Url;
                            break;
                        }
                    }
                    $this->info('Add tunnel ok,type:' . $payload->Protocol . " url:\t" . $payload->Url);
                }
                break;
            default:
                break;
        }
    }

    public function startTimer()
    {
        if ($this->timerId) {
            return;
        }
        $this->timerId = $this->loop->addPeriodicTimer(20, function () {
            // if (!$this->isConnected()) {
            //     return;
            // }
            $this->sendPack($this->ping());
        });
    }

    public function onError()
    {
        $this->info("onError");

        $this->clearTimer();
        
        $this->info("连接失败，10s后重试");
        
        $this->reconnect();
    }

    protected function clearTimer()
    {
        $this->timerId && $this->loop->cancelTimer($this->timerId);
        $this->timerId = null;
    }

    public function onClose()
    {
        $this->info("onClose");

        $this->clearTimer();

        $this->info("断线重连");

        $this->reconnect();
    }

    public function reconnect()
    {
        $this->clientId = null;
        $this->recvBuffer = '';
        $this->loop->addTimer(10, function() {
            $this->start();
        });
    }

    public function registerTunnels()
    {
        //映射到通道
        foreach ($this->tunnelList as $i => $tunnel) {
            $this->sendPack($tunnel->register());
        }
    }

    private function auth()
    {
        return [
            'Type' => 'Auth',
            'Payload' => [
                'ClientId'  => $this->clientId,
                'OS'        => 'darwin',
                'Arch'      => 'amd64',
                'Version'   => '2',
                'MmVersion' => '1.7',
                'User'      => $this->authToken,
                'Password'  => '',
            ],
        ];
    }

    private function ping()
    {
        return [
            'Type' => 'Ping',
            'Payload' => (object) [],
        ];
    }
}