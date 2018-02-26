<?php

namespace Drmer\Pgrok\Commands;

use Illuminate\Console\Command;
use Drmer\Pgrok\ControlClient;
use React\EventLoop\Factory as LoopFactory;

class PgrokCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pgrok';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment("Pgrok for Laravel");

        $config = [
            'host'       => config('pgrok.host'),
            'port'       => config('pgrok.port'),
            'token'      => config('pgrok.token'),
            'verify_ssl' => config('pgrok.verify_ssl'),
        ];

        $loop = LoopFactory::create();

        new ControlClient($loop, $config, config('pgrok.tunnels'));

        $loop->run();
    }
}