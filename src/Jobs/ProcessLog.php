<?php

namespace Litermi\Elasticlog\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client as ClientHttp;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Litermi\Elasticlog\Services\SendElasticServices;

class ProcessLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $connection_name = 'sqs';
    protected $Record;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($record)
    {
        $this->Record = $record;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $record =  $this->Record;

        $message = $record['message'];
        $formated = $record['formatted'];
        $level = $formated['level'];
        $token =  $formated['token'];

        SendElasticServices::execute('SQS',$message,$token);

        //release for more than 2 attempts
        if ($this->attempts() > 2) {
            $this->release();
        }
    }
}
