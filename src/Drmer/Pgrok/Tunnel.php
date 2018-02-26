<?php

namespace Drmer\Pgrok;

class Tunnel
{
    public $protocol = '';
    public $hostname = '';
    public $subdomain = '';
    public $remotePort = 0;
    public $localHost = null;
    public $localPort = 80;

    public $requestId = null;
    public $url = null;

    public function __construct($config)
    {
        $this->protocol = $config['protocol'];
        $this->hostname = $config['hostname'];
        $this->subdomain = $config['subdomain'];
        $this->remotePort = $config['rport'];
        $this->localHost = $config['lhost'];
        $this->localPort = $config['lport'];
    }

    public function register()
    {
        $this->requestId = str_random(16);
        return [
            'Type' => 'ReqTunnel',
            'Payload' => [
                'ReqId'      => $this->requestId,
                'Protocol'   => $this->protocol,
                'Hostname'   => $this->hostname,
                'Subdomain'  => $this->subdomain,
                'HttpAuth'   => '',
                'RemotePort' => $this->remotePort,
            ],
        ];
    }
}