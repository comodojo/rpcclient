<?php namespace Comodojo\RpcClient\Processor;

use \Psr\Log\LoggerInterface;
use \Comodojo\RpcClient\RpcRequest;
use \Comodojo\Xmlrpc\XmlrpcEncoder;
use \Comodojo\Xmlrpc\XmlrpcDecoder;
use \Comodojo\Exception\RpcException;
use \Comodojo\Exception\XmlrpcException;
use \Exception;

class XmlProcessor extends AbstractProcessor {

    private $encoder;

    private $decoder;

    private $isMulticall = false;

    public function __construct($encoding, LoggerInterface $logger) {

        parent::__construct($encoding, $logger);

        $this->encoder = new XmlrpcEncoder();

        $this->decoder = new XmlrpcDecoder();

    }

    public function encode($requests) {

        $requests = array_values($requests);

        $this->isMulticall = sizeof($requests) > 1 ? true : false;

        try {

            $payload = $this->isMulticall ? $this->encodeMulticall($requests) : $this->encodeSingleCall($requests[0]);

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

        return $this->isMulticall ? self::normalizeContent($content) : $content;

    }

    private function encodeSingleCall(RpcRequest $request) {

        $this->logger->notice("Performing a single XML call");

    	$this->logger->debug("Data dump before encoding", $request->toArray());

        try {

        	// encoding

        	foreach ( $request->getSpecialTypes() as $key => $value ) {

                $this->encoder->setValueType($key, $value);

            }

            $encoded_request = $this->encoder->encodeCall($request->getMethod(), $request->getParameters());

        } catch (XmlrpcException $xe) {

            throw $xe;

        }

        $this->logger->debug("Data dump after encoding: ".$encoded_request);

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

            $encoded_requests = $this->encoder->setEncoding($this->getEncoding())->encodeMulticall($composed_requests);

        } catch (XmlrpcException $xe) {

            throw $xe;

        }

        return $encoded_requests;

    }

    private static function normalizeContent($content) {

        return array_map(function($value) {

            if (
                is_array($value) &&
                sizeof($value) == 1 &&
                isset($value[0])
            ) {
                return $value[0];
            }

            return $value;

        }, $content);

    }

}
