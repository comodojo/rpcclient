<?php namespace Comodojo\RpcClient\Processor;

use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\RpcException;
use \Comodojo\RpcClient\RpcRequest;
use \Exception;

class JsonProcessor implements ProcessorInterface {

    private $encoding;

    private $logger;

    private $encoder;

    private $requests;

    private $ids;

    public function __construct($encoding, LoggerInterface $logger) {

        $this->encoding = $encoding;

        $this->logger = $logger;

    }

    public function encode($requests) {

        $this->requests = $requests

        $payload = array();

        foreach ($requests as $request) {

            list($composed, $rid) = self::composeJsonRequest($request);

            $payload[] = $composed;

            if ( $rid !== null ) $this->ids[] = $rid;

        }

        return ( sizeof($payload > 1) ) ? json_encode($payload) : json_encode($payload[0]);

    }

    public function decode($response) {

        try {

            $content = json_decode($response, true);

            if ( is_null($content) ) throw new Exception("Incomprehensible or empty response");

            if ( sizeof($this->requests) == 0 ) {

                $return = true;

            } else if ( sizeof($this->requests) == 1 ) {

                if ( $content["id"] != $this->ids[0] ) throw new Exception("Invalid response ID received");

                $return = $content["result"];

            } else {

                $batch_content = array();

                foreach ( $this->ids as $key => $id ) {

                    if ( !isset($content[$key]) ) $batch_content[$key] = array("error" => array("code" => null, "message" => "Empty response"));

                    else if ( isset($content[$key]["error"]) ) $batch_content[$key] = array("error" => $content["error"]);

                    else if ( !isset($content[$key]["id"]) ) $batch_content[$key] = array("error" => array("code" => null, "message" => "Malformed response received"));

                    else if ( $content[$key]["id"] != $id ) $batch_content[$key] = array("error" => array("code" => null, "message" => "Invalid response ID received"));

                    else $batch_content[$key] = array("result" => $content[$key]["result"]);

                }

                $return = $batch_content;

            }

        } catch (Exception $xe) {

            throw $xe;

        }

        return $content;

    }

    private static function composeJsonRequest(Request $request) {

        $return = array(
            "jsonrpc"   =>  "2.0",
            "method"    =>  $request->getMethod(),
            "params"    =>  $request->getParameters()
        );

        $rid = $request->getId();

        if ( $rid === true ) {

            $id = $return["id"] = $request->getUniqueId(),

        } else if ( is_scalar($rid) ) {

            $id = $return["id"] = $rid;

        } else {

            $id = null;

        }

        return array($return, $id);

    }

}
