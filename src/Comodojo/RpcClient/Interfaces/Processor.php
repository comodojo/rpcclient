<?php namespace Comodojo\RpcClient\Interfaces;

use \Psr\Log\LoggerInterface;

interface Processor {

    public function __construct($encoding, LoggerInterface $logger);

    public function encode($requests);

    public function decode($response);

}
