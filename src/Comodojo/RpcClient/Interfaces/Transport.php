<?php namespace Comodojo\RpcClient\Interfaces;

use \Psr\Log\LoggerInterface;

interface Transport {

    /**
     * Perform call using transport layer
     *
     * @param LoggerInterface $looger
     * @param string $data
     * @param string $content_type
     * @param string|bool $encrypt
     *
     * @return string
     *
     * @throws \Comodojo\Exception\RpcException
     * @throws \Exception
     */
    public function performCall(
        LoggerInterface $logger,
        $data,
        $content_type,
        $encrypt
    );

}
