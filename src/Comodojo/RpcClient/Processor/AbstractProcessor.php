<?php namespace Comodojo\RpcClient\Processor;

use Comodojo\RpcClient\Components\Encoding as EncodingTrait;
use \Psr\Log\LoggerInterface;

abstract class AbstractProcessor implements ProcessorInterface {

    use EncodingTrait;

    protected $logger;

    public function __construct($encoding, LoggerInterface $logger) {

        $this->setEncoding($encoding);

        $this->logger = $logger;

    }

    abstract public function encode($requests);

    abstract public function decode($response);

}
