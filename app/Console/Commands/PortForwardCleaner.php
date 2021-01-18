<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PortForward;
use Carbon\Carbon;

class PortForwardCleaner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PortForwardCleaner:handle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清理过期的转发.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->HandlerServiceClient = [
            '54.178.78.36' => new \Bluehead\V2ray\Core\App\Proxyman\Command\HandlerServiceClient('127.0.0.1:43200', ['credentials' => \Grpc\ChannelCredentials::createInsecure()]),
            '13.124.203.36' => new \Bluehead\V2ray\Core\App\Proxyman\Command\HandlerServiceClient('127.0.0.1:43201', ['credentials' => \Grpc\ChannelCredentials::createInsecure()]),
            '102.223.72.241' => new \Bluehead\V2ray\Core\App\Proxyman\Command\HandlerServiceClient('127.0.0.1:43202', ['credentials' => \Grpc\ChannelCredentials::createInsecure()]),
        ];
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $records = PortForward::where(['enable' => true])->where('created_at', '<', Carbon::now()->subDays(7))->get();
        foreach($records as $record){
            $tag = $record->id;
            $inbound = (new \Bluehead\V2ray\Core\App\Proxyman\Command\RemoveInboundRequest())->setTag($tag);
            $call = $this->HandlerServiceClient['127.0.0.1']->RemoveInbound($inbound);
            $resp = PortForward::where('_id', '=', $tag)->update(['enable' => false]);
        }
        return 0;
    }
}
