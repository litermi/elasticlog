<?php

namespace Litermi\Elasticlog\Services;

use GuzzleHttp\Client as ClientHttp;
use GuzzleHttp\RequestOptions;
use Exception;

class SendElasticServices
{
    public static function execute($type, $tag, $request)
    {
        try {
            //check if defined configuration for elasticsearch
            $elastic_server = env('ELASTIC_URL') . ':' . env('ELASTIC_PORT');

            if (isset($elastic_server)) {
                //get configuration from env
                $env = env('APP_ENV');
                //get elastic timezone
                $elastic_tz = env('ELASTIC_TIMEZONE');

                //create DateTime Object
                $date_time = new \DateTime();
                //get date from object
                $date = $date_time->format('Y-m-d');
                //transform to elastic date format
                $date_time = str_replace(" ", "T", $date_time->format('Y-m-d H:i:s'));
                //Add timezone
                $date_time .= $elastic_tz;

                //create access token
                $base         = env('ELASTIC_USER') . ':' . env('ELASTIC_PASS');
                $aliasProject = env('ALIAS_PROJECT', 'rapi');
                $token        = 'Basic ' . Base64_encode($base);
                $time         = time() . rand(99, 999);

                //create url for index
                $elasticURL = "{$elastic_server}/{$aliasProject}-{$env}-log-{$date}/log/{$time}";
                $headers    = [
                    'Content-type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'authorization' => $token,
                ];
                //Disable SSL verification for elastic server
                $client  = new ClientHttp([ 'curl' => [ CURLOPT_SSL_VERIFYPEER => false, ], ]);
                $tag = str_replace("\\", "", $tag);
                $payload = [
                    'date' => $date_time,
                    'type' => $type,
                    'log'  => $tag,
                    'tk'   => $request,
                ];

                $response = $client->post(
                    $elasticURL, [
                        'headers'            => $headers,
                        RequestOptions::JSON => $payload,
                    ]
                );

            }

        }
        catch(Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
