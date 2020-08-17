<?php

namespace Litermi\Elasticlog\Services\Logs;

use Monolog\Handler\AbstractSyslogHandler;
use Monolog\Logger;
use Monolog\Handler\SyslogUdp\UdpSocket;

class SendLogUdpJsonHandler extends AbstractSyslogHandler
{
    protected $socket;
    protected $ident;

    /**
     * @param string  $host
     * @param int     $port
     * @param mixed   $facility
     * @param int     $level  The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble Whether the messages that are handled can bubble up the stack or not
     * @param string  $ident  Program name or tag for each log message.
     */
    public function __construct(
        $facility = LOG_USER,
        $level = Logger::DEBUG,
        $bubble = true,
        $ident = 'php'
    ) {
        parent::__construct($facility, $level, $bubble);

        $this->ident = $ident;

        $host = config('elastic.elastic_url');
        $port = config('elastic.elastic_port');
        $this->socket = new UdpSocket($host, $port ? : 5000);
    }

    public function write(array $record)
    {
        $jsonRecord = \GuzzleHttp\json_encode($record);
        $this->socket->write($jsonRecord, '');
    }

    public function close()
    {
        $this->socket->close();
    }

    /**
     * Inject your own socket, mainly used for testing
     */
    public function setSocket($socket)
    {
        $this->socket = $socket;
    }

}

