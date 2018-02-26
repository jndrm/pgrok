<?php

return [
    'host' => 'ngrok.local',
    'port' => '4443',
    'token' => '',
    'verify_ssl' => false,
    'tunnels' => [
        [
            'protocol' => 'http',
            'hostname' => '',
            'subdomain' => 'test',
            'rport' => 0,
            'lhost' => '127.0.0.1',
            'lport' => 80
        ],
        // array(
        //     'protocol' => 'http',
        //     'hostname' => '',
        //     'subdomain' => 'xxx',
        //     'rport' => 0,
        //     'lhost' => '127.0.0.1',
        //     'lport' => 80
        // ),
        // array(
        //     'protocol' => 'tcp',
        //     'hostname' => '',
        //     'subdomain' => '',
        //     'rport' => 57715,
        //     'lhost' => '127.0.0.1',
        //     'lport' => 22
        // ),
    ],
];