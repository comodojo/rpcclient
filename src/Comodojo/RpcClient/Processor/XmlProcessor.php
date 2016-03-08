<?php namespace Comodojo\RpcClient\Processor;

use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\RpcException;
use \Comodojo\RpcClient\RpcRequest;
use \Comodojo\Xmlrpc\XmlrpcEncoder;
use \Comodojo\Xmlrpc\XmlrpcDecoder;
use \Exception;

class XmlProcessor implements ProcessorInterface {

    private $encoding;

    private $logger;

    private $encoder;

    private $decoder;

    private $requests;

    public function __construct($encoding, LoggerInterface $logger) {

        $this->encoding = $encoding;

        $this->logger = $logger;

        $this->encoder = new XmlrpcEncoder();

        $this->decoder = new XmlrpcDecoder();

    }

    public function encode($requests) {

        $this->requests = $requests;

        try {

            $payload = ( sizeof($requests) > 1 ) ? $this->encodeMulticall($requests) : $this->encodeSingleCall($requests[0]);

        } catch (XmlrpcException $xe) {

            throw $xe;

        }

        return $payload;

    }

    public function decode($response) {

        try {

            $content = $this->decoder->decodeResponse($response);

            if ( $this->decoder->isFault() ) throw new RpcException($content['faultString'], $content['faultCode']);

        } catch (XmlrpcException $xe) {

            throw $xe;

        }

        return $content;

    }

    private function encodeSingleCall(RpcRequest $request) {

        $this->requests = $request;

    	$this->logger->notice("Performing a single XML call");

    	$this->logger->debug("Data dump before encoding", $request);

        try {

        	// encoding

        	foreach ( $request->getSpecialTypes() as $key => $value ) {

                $this->encoder->setValueType($key, $value);

            }

            $encoded_request = $this->encoder->encodeCall($request->getMethod(), $request->getParameters());

        } catch (XmlrpcException $xe) {

            throw $xe;

        }

        $this->logger->debug("Data dump after encoding", $encoded_request);

        return $encoded_request;

    }

    /**
     * Perform an xml multicall
     *
     * @param   array   $requests
     *
     * @return  array
     *
     * @throws \Comodojo\Exception\RpcException
     * @throws \Comodojo\Exception\HttpException
     * @throws \Comodojo\Exception\XmlrpcException
     * @throws \Exception
     */
    private function encodeMulticall($requests) {

        $this->requests = $requests;

        $composed_requests = array();

    	$this->logger->notice("Performing an XML multicall");

    	$this->logger->debug("Data dump before encoding", $requests);

        foreach ($requests as $request) {

            $composed_requests[] = array($request->getMethod(), $request->getParameters());

            foreach ( $request->getSpecialTypes() as $key => $value ) {

                $this->encoder->setValueType($key, $value);

            }

        }

        try {

            $encoded_requests = $this->encoder->setEncoding($this->encoding)->encodeMulticall($composed_requests);

        } catch (XmlrpcException $xe) {

            throw $xe;

        }

        return $encoded_request;

    }

}
