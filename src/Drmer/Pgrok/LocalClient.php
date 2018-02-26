<?php

namespace Drmer\Pgrok;

class LocalClient extends Client
{
    protected $proxy = null;

    protected $recvBuffer = '';

    protected $timerId = null;

    protected $tunnel = null;

    protected $typeName = 'prv';

    public function __construct($loop, $proxy, $tunnel)
    {
        $this->proxy = $proxy;
        $this->tunnel = $tunnel;

        parent::__construct($loop);

        $this->info("Joined with connection pxy:{$proxy->name}");
    }

    public function start()
    {
        $this->connect("tcp://{$this->tunnel->localHost}:{$this->tunnel->localPort}");
    }

    public function onConnect()
    {
        $cli = $this->connection;

        // 连接成功之后绑定Proxy连接
        $proxyConnection = $this->proxy->getConnection();

        $cli->pipe($proxyConnection);
        $proxyConnection->pipe($cli);

        // 连接成功，将缓冲区的内容全都发送给客户端
        if ($this->recvBuffer) {
            $this->send($this->recvBuffer);
            $this->recvBuffer = null;
        }
        $proxyConnection->resume();
    }

    public function toSend($buffer)
    {
        if (!$buffer) {
            return;
        }
        $this->recvBuffer = $buffer;
    }

    public function onError()
    {
        // 清空本地缓存
        $this->recvBuffer = null;

        // 返回错误页面
        $body = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Web服务错误</title><meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no"><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><style>html,body{height:100%%}body{margin:0;padding:0;width:100%%;display:table;font-weight:100;font-family:"Microsoft YaHei",Arial,Helvetica,sans-serif}.container{text-align:center;display:table-cell;vertical-align:middle}.content{border:1px solid #ebccd1;text-align:center;display:inline-block;background-color:#f2dede;color:#a94442;padding:30px}.title{font-size:18px}.copyright{margin-top:30px;text-align:right;color:#000}</style></head><body><div class="container"><div class="content"><div class="title">隧道 %s 无效<br>无法连接到<strong>%s</strong>. 此端口尚未提供Web服务</div></div></div></body></html>';
        $html = sprintf($body, $payload['Url'], $loacladdr['lhost'] .':' . $loacladdr['lport']);
        $header = "HTTP/1.0 502 Bad Gateway\r\n";
        $header .= "Content-Type: text/html\r\n";
        $header .= "Content-Length: %d\r\n";
        $header .= "\r\n%s";
        $this->proxy->send(sprintf($header, strlen($html), $html));

        $this->proxy->close();

        $this->stopTimer();
    }

    public function onReceive($data)
    {
    }

    public function onClose()
    {
        $this->info("Closing");
    }
}