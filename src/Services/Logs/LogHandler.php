<?php

namespace litermi\elasticlog\Services\Logs;

use litermi\elasticlog\Events\Logs\LogMonologEvent;
use Illuminate\Support\Facades\Cache;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use GuzzleHttp\Client as ClientHttp;
use GuzzleHttp\RequestOptions;
use litermi\elasticlog\Jobs\ProcessLog;



class LogHandler extends AbstractProcessingHandler
{
    public function __construct($level = Logger::DEBUG)
    {
        parent::__construct($level);
    }
    protected function write(array $record)
    {

        try {
            ProcessLog::dispatch($record);
        } catch (\Exception $e) {

        }
    }
    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter()
    {
        return new LogFormatter();
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
            $elasticURL = $elastic_server . '/rapi-' . $env . '-log-' . $date . '/log/' . time();
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
                $response = $client->post($elasticURL, [
                    'headers' => $headers,
                    RequestOptions::JSON => $payload
                ]);
            } catch (\Exception $e) {

            }
        }
    }


    public static function LogToSlack($message)
    {
        try {
            //slack url
            $url = env('LOG_SLACK_WEBHOOK_URL_TESTS');


            $headers = [
                'Content-Type' => 'application/json',
            ];
            $client = new ClientHttp([
                'headers' => $headers
            ]);
            $response = $client->post($url, [
                RequestOptions::JSON => ['text' => $message]
            ])->getBody()->getContents();
        } catch (\Exception $e) {
            //log error to slack

        }
    }
}
