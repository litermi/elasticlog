<?php

namespace Litermi\Elasticlog\Services;

class SendElasticServices
{
    public static function execute($type,$tag,$request)
    {
        //check if defined configuration for elasticsearch
        $elastic_server  = config('elastic.elastic_url').':'. config('elastic.elastic_port');
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
            $base = config('elastic.elastic_user').':'.config('elastic.elastic_pass');
            $aliasProject = config('elastic.elastic_alias_project');
            $token ='Basic '. Base64_encode($base);
            $time = time(). rand(99,999);

            //create url for index
            $elasticURL = "{$elastic_server}/{$aliasProject}-{$env}-log-{$date}/log/{$time}";
            $headers = [
                'Content-type' => 'application/json',
                'Accept' => 'application/json',
                'authorization' => $token,
            ];
            //Disable SSL verification for elastic server
            $client = new ClientHttp(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false,),));

            $payload = ['date' => $date_time,
                        'type' => $type,
                        'log' => $tag,
                        'tk' => $request];



            try {
                $response = $client->post($elasticURL, [
                    'headers' => $headers,
                    RequestOptions::JSON => $payload
                ]);
                Log::channel('stderr')->info('log sent');
            } catch (\Exception $exception) {
                Log::channel('stderr')->info($exception->getMessage());

                $nameCache = 'cacheErrorElastic';
                $cache     = Cache::get($nameCache);
                if ($cache === null) {
                    Cache::put($nameCache, 'error', 30);
                    throw new Exception($exception->getMessage());
                }
            }
        }
    }
}
