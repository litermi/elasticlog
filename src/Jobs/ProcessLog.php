<?php

namespace Litermi\Elasticlog\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client as ClientHttp;
use GuzzleHttp\RequestOptions;

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

        $this->sendToElastic('SQS', $message, $token);

        //release for more than 2 attempts
        if ($this->attempts() > 2) {
            $this->release();
        }
    }



    public static function sendToElastic($type, $tag, $request)
    {
        //check if defined configuration for elasticsearch
        $elastic_server  = config('elastic.elastic_url') . ':' . config('elastic.elastic_port');
        if (isset($elastic_server)) {
            //get configuration from env
            $env = config('app.env');
            //get elastic timezone
            $elastic_tz  = config('elastic.elastic_timezone');

            //create DateTime Object
            $date_time = new \DateTime();
            //get date from object
            $date = $date_time->format('Y-m-d');
            //transform to elastic date format
            $date_time = str_replace(" ", "T", $date_time->format('Y-m-d H:i:s'));
            //Add timezone
            $date_time .= $elastic_tz;


            //create access token
            $base = config('elastic.elastic_user') . ':' . config('elastic.elastic_pass');
            $token = 'Basic ' . Base64_encode($base);

            //create url for index
            $elasticURL = $elastic_server . '/' . strtolower(config('app.name')) . '-' . $env . '-log-' . $date . '/log/' . time();
            $headers = [
                'Content-type' => 'application/json',
                'Accept' => 'application/json',
                'authorization' => $token,
            ];
            //Disable SSL verification for elastic server
            $client = new ClientHttp(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false,),));

            $payload = [
                'date' => $date_time,
                'type' => $type,
                'log' => $tag,
                'tk' => $request
            ];

            try {
                $response = $client->postAsync($elasticURL, [
                    'headers' => $headers,
                    RequestOptions::JSON => $payload
                ]);
            } catch (\Exception $e) {
                // no actions for exception
            }
        }
    }
}
