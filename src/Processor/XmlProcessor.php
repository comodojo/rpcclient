<?php namespace Comodojo\RpcClient\Processor;

use \Psr\Log\LoggerInterface;
use \Comodojo\RpcServer\Transport\Request;
use \Comodojo\Exception\RpcException;
use \Exception;

class XmlProcessor {

	private $encoder = null;

	private $decoder = null;

	private $transport = null;

	private $encryption = null;

	private $logger = null;

	public function __construct(Request $transport, $encoding, $encryption, LoggerInterface $logger) {

		$this->encoder = new XmlrpcEncoder();

		$this->decoder = new XmlrpcDecoder();

		$this->transport = $transport;

		$this->encryption = $encryption;

		// set the encoding (currently available for encoder only)

		$this->encoder->setEncoding($encoding);

		// $this->decoder->setEncoding($encoding);

		$this->logger = $logger;

	}

	/**
     * Perform an xml call
     *
     * @param   array   $request
     * 
     * @return  mixed
     *
     * @throws \Comodojo\Exception\RpcException
     * @throws \Comodojo\Exception\HttpException
     * @throws \Comodojo\Exception\XmlrpcException
     * @throws \Exception
     */
    public function call($request, $special_types = array()) {

    	$this->logger->notice("Performing a single XML call");

    	$this->logger->debug("Data dump before encoding", $request);

        try {

        	// encoding

        	foreach ( $special_types[0] as $key => $value ) $this->encoder->setValueType($key, $value);

            $encoded_request = $this->encoder->encodeCall($request["METHOD"], $request["PARAMETERS"]);

            // calling

            $received = $this->transport->performCall($real_request, 'text/xml', $encryption);

            // decoding
            
            $decoded = $this->decoder->decodeResponse($received);

			if ( $decoder->isFault() ) throw new RpcException($decoded['faultString'], $decoded['faultCode']);

        } catch (RpcException $re) {

            throw $re;

        } catch (HttpException $he) {

            throw $he;

        } catch (XmlrpcException $xe) {

            throw $xe;

        } catch (Exception $e) {

            throw $e;

        }

        $this->logger->debug("Data dump after decoding", $request);

        return $decoded;

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
    private function multicall($requests) {

        $requests = self::splitMulticallXmlRequests($requests);

        try {
        
            if ( self::phpXmlrpcAvailable() ) {

                $request = xmlrpc_encode_request("system.multicall", $requests, array(
                    'encoding' => $this->encoding,
                    'verbosity'=> 'no_white_space',
                    'version'  => 'xmlrpc'
                ));

            } else {

                $encoder = new XmlrpcEncoder();

                $request = $encoder->setEncoding($this->encoding)->encodeMulticall($requests);

            }

            $received = $this->performCall($request, 'text/xml');

            if ( self::phpXmlrpcAvailable() ) {

                $decoded = xmlrpc_decode($received);

                if ( is_array($decoded) && xmlrpc_is_fault($decoded) ) throw new RpcException($decoded['faultString'], $decoded['faultCode']);

                $return = $decoded;

            } else {

                $decoder = new XmlrpcDecoder();

                $decoded = $decoder->decodeResponse($received);

                if ( $decoder->isFault() ) throw new RpcException($decoded['faultString'], $decoded['faultCode']);

                $return = $decoded;

            }


        } catch (HttpException $he) {

            throw $he;

        } catch (RpcException $re) {

            throw $re;

        } catch (XmlrpcException $xe) {

            throw $xe;

        } catch (Exception $e) {

            throw $e;

        }

        return $return;

    }

}