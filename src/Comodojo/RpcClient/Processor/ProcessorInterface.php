<?php namespace Comodojo\RpcClient\Processor;

use \Psr\Log\LoggerInterface;

interface ProcessorInterface {

    public function __construct($encoding, LoggerInterface $logger);

    public function encode($requests);

    public function decode($response);

}
