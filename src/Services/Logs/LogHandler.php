<?php

namespace Litermi\Elasticlog\Services\Logs;

use litermi\elasticlog\Events\Logs\LogMonologEvent;
use Illuminate\Support\Facades\Cache;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use GuzzleHttp\Client as ClientHttp;
use GuzzleHttp\RequestOptions;
use Litermi\Elasticlog\Jobs\ProcessLog;



class LogHandler extends AbstractProcessingHandler
{
    public function __construct($level = Logger::DEBUG)
    {
        parent::__construct($level);
    }
    protected function write(array $record)
    {

        try {
            ProcessLog::dispatch($record)->onConnection('sync');
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
}
