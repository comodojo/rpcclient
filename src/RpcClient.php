<?php namespace Comodojo\RpcClient;

use \Comodojo\Exception\RpcException;
use \Comodojo\Exception\HttpException;
use \Comodojo\Exception\XmlrpcException;
use \Exception;
use \Comodojo\Httprequest\Httprequest;
use \Comodojo\Xmlrpc\XmlrpcEncoder;
use \Comodojo\Xmlrpc\XmlrpcDecoder;
use \Crypt_AES;

/** 
 * Comodojo RPC client. It's able to talk in XML and JSON (2.0).
 *
 * It optionally supports a not standard comodojo encrypted transport
 * 
 * @package     Comodojo PHP Backend
 * @author      comodojo.org
 * @copyright   __COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version     __CURRENT_VERSION__
 * @license     GPL Version 3
 */

 /*
  * 
  * 2003 Unknown transport
  * 2004 Wrong method (not scalar)
  * 2005 Bad parameters (not array)
  * 2006 Invalid request ID
  * 2007 Invalid response ID:
  * 2008 Error processing request: 
  */

class RpcClient {
    
    /**
     * Default disabled: is faster but does not work for base64 and datetime values
     *
     * @var bool
     */
    static private $useNativeEncoder = false;

    /**
     * Remote host address (complete url)
     *
     * @var string
     */
    private $server = false;
    
    /**
     * Remote host port
     *
     * @var int
     */
    private $port = 80;
    
    /**
     * Enable comodojo encrypted transport
     *
     * @var mixed
     */
    private $encrypt = false;
    
    /**
     * RPC transport
     *
     * @var string
     */
    private $transport = 'XML';
    
    /**
     * HTTP method to use
     *
     * @var string
     */
    private $http_method = 'POST';
    
    /**
     * Characters encoding
     *
     * @var string
     */
    private $encoding = 'utf-8';

    /**
     * Autoclean requests
     *
     * @var string
     */
    private $autoclean = true;

    /**
     * Supported RPC protocols
     *
     * @var string
     */
    private $supported_protocols = array("XML","JSON");

    /**
     * Supported HTTP methods protocols
     *
     * @var string
     */
    private $supported_http_methods = array("GET","POST","PUT","DELETE");

    // internals

    private $requests = array();

    private $special_types = array();
    
    public function __construct($server) {

        if ( empty($server) ) throw new Exception("Invalid RPC server address");

        $this->server = $server;

    }

    final public function transport($protocol) {

        $proto = strtoupper($protocol);

        if ( !in_array($proto, $this->supported_protocols) ) throw new Exception("Invalid RPC protocol");

        $this->transport = $proto;

        return $this;

    }

    final public function encrypt($key) {

        if ( empty($key) ) throw new Exception("Shared key cannot be empty");

        $this->encrypt = $key;

        return $this;

    }

    final public function port($port) {

        $this->port = filter_var($port, FILTER_VALIDATE_INT, array(
            "options" => array(
                "min_range" => 1,
                "max_range" => 65535,
                "default" => 80
                )
            )
        );

        return $this;

    }

    final public function encode($encoding) {

        $this->encoding = $encoding;

        return $this;

    }

    final public function httpMethod($method) {

        $method = strtoupper($method);

        if ( !in_array($method, $this->supported_http_methods) ) throw new Exception("Invalid HTTP method");

        $this->httpMethod = $method;

        return $this;

    }

    final public function request($method, $parameters, $id=true) {

        if ( empty($method) OR !is_scalar($method) ) throw new Exception("Invalid method (not scalar or empty)");
        
        if ( !is_array($parameters) ) throw new Exception("Bad parameters (not array)");

        array_push($this->requests, array(
            "METHOD"    =>  $method,
            "PARAMETERS"=>  $parameters,
            "ID"        =>  $id
        ));

        return $this;

    }

    final public function autoclean($mode=true) {

        $this->autoclean = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    final public function setValueType(&$value, $type) {

        if ( empty($value) OR !in_array(strtolower($type), array("base64","datetime")) ) throw new Exception("Invalid value type");

        $this->special_types[$value] = strtolower($type);

        return $this;

    }

    /**
     * Send request(s) to server
     *
     */
    public function send() {

        if ( sizeof($this->requests) == 0 ) throw new Exception("Empty request, cannot perform call");

        try {

            switch ( $this->transport ) {

                case 'XML':
                    
                    $response = sizeof($this->requests) == 1 ? $this->xmlCall($this->requests[0]) : $this->xmlMulticall($this->requests);
                
                    break;
                    
                case 'JSON':

                    $response = sizeof($this->requests) == 1 ? $this->jsonCall($this->requests[0]) : $this->jsonMulticall($this->requests);

                    break;
                
                default:

                    throw new Exception("Invalid RPC transport protocol");

                    break;

            }

        } catch (HttpException $he) {

            throw $he;

        }  catch (RpcException $re) {

            throw $re;

        } catch (XmlrpcException $xe) {

            throw $xe;

        } catch (Exception $e) {

            throw $e;

        }
        
        if ( $this->autoclean ) $this->cleanRequests();

        return $response;

    }

    private function jsonCall($request) {

        list($json_request, $id) = self::composeJsonRequest($request);

        try {
            
            $received = $this->performCall(json_encode($json_request), 'application/json');

            if ( $id !== null ) {

                $response = json_decode($received);

                if ( is_null($response) ) throw new Exception("Incomprehensible or empty response");

                else if ( isset($response["error"])) throw new RpcException($response["error"]["message"], $response["error"]["code"]);
                
                else if ( $response["id"] != $id ) throw new Exception("Invalid response ID received");

                else $return = $response["result"];

            }

            else $return = true;

        } catch (HttpException $he) {

            throw $he;

        } catch (RpcException $re) {

            throw $re;

        } catch (Exception $e) {

            throw $e;

        }

        return $return;

    }

    private function jsonMulticall($requests) {

        $expected_ids = array();

        $batch_request = array();

        $batch_response = array();

        foreach ($requests as $request) {
            
            list($json_request, $id) = self::composeJsonRequest($request);

            if ( $id !== null ) $expected_ids[] = $id;

            $batch_request[] = $request;

        }

        try {
            
            $received = $this->performCall(json_encode($json_request), 'application/json');

        } catch (HttpException $he) {

            throw $he;

        } catch (Exception $e) {

            throw $e;

        }

        if ( !empty($expected_ids) ) {

            $response = json_decode($received);

            if ( is_null($response) ) throw new Exception("Incomprehensible or empty response");

            foreach ($expected_ids as $key => $id) {
                
                if ( !isset($response[$key]) ) $batch_response[$key] = array("error" => array("code" => null, "message" => "Empty response"));

                else if ( isset($response[$key]["error"])) $batch_response[$key] = array("error" => $response["error"]);

                else if ( $response[$key]["id"] != $id ) $batch_response[$key] = array("error" => array("code" => null, "message" => "Invalid response ID received"));

                else $batch_response[$key] = array("result" => $response[$key]["result"]);

            }

        } else $batch_response = true;

        return $batch_response;

    }

    /**
     * @todo    Fix xmlrpc_encode_request base64 not recognized correctly
     *
     */private function xmlCall($request) {

        try {
        
            if ( self::phpXmlrpcAvailable() ) {

                foreach ($this->special_types as $key => $value) xmlrpc_set_type($key, $value);

                $real_request = xmlrpc_encode_request($request["METHOD"], $request["PARAMETERS"], array(
                    'encoding' => $this->encoding,
                    'verbosity'=> 'no_white_space',
                    'version'  => 'xmlrpc'
                ));

            } else {

                $encoder = new XmlrpcEncoder();

                foreach ($this->special_types as $key => $value) $encoder->setValueType($key, $value); 

                $real_request = $encoder->setEncoding($this->encoding)->encodeCall($request["METHOD"], $request["PARAMETERS"]);

            }

            $received = $this->performCall($real_request, 'application/xml');

            if ( self::phpXmlrpcAvailable() ) {

                $decoded = xmlrpc_decode($received);

                if (is_array($decoded) && xmlrpc_is_fault($decoded)) throw new RpcException($decoded['faultString'], $decoded['faultCode']);

                $return = $decoded;

            } else {

                $decoder = new XmlrpcDecoder();

                $decoded = $decoder->decodeResponse($received);

                if ( $decoder->isFault() ) throw new RpcException($decoded[0]['faultString'], $decoded[0]['faultCode']);

                $return = $decoded[0];

            }


        } catch (RpcException $re) {

            throw $re;

        } catch (HttpException $he) {

            throw $he;

        } catch (XmlrpcException $xe) {

            throw $xe;

        } catch (Exception $e) {

            throw $e;

        }

        return $return;

    }

    private function xmlMulticall($requests) {

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

            $received = $this->performCall($request, 'application/xml');

            if ( self::phpXmlrpcAvailable() ) {

                $decoded = xmlrpc_decode($received);

                if (is_array($decoded) && xmlrpc_is_fault($decoded)) throw new RpcException($decoded['faultString'], $decoded['faultCode']);

                $return = $decoded;

            } else {

                $decoder = new XmlrpcDecoder();

                $decoded = $decoder->decodeResponse($received);

                if ( $decoder->isFault() ) throw new RpcException($decoded[0]['faultString'], $decoded[0]['faultCode']);

                $return = $decoded[0];

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

    private function performCall($data, $content_type) {

        if ( $this->encrypt !== false ) {

            $aes = new Crypt_AES();

            $aes->setKey($this->encrypt);

            $data = 'comodojo_encrypted_envelope-'.$aes->encrypt($data);

        }
        
        try {

            $sender = new Httprequest($this->server);

            $response = $sender->setPort($this->port)->setHttpMethod($this->http_method)->setContentType($content_type)->send($data);

        }
        catch (HttpException $he) {

            throw $he;

        }

        if ( $this->encrypt !== false ) return $aes->decrypt($response);

        else return $response;

    }

    private function cleanRequests() {

        $this->requests = array();

        $this->special_types = array();

    }

    static private function phpXmlrpcAvailable() {

        return ( function_exists('xmlrpc_encode_request') AND self::$useNativeEncoder );

    }

    static private function splitMulticallXmlRequests($requests) {

        $return = array();

        if ( self::phpXmlrpcAvailable() ) {

            foreach ($requests as $request) {
                
                array_push($return, array(
                    "methodName"    =>  $request["METHOD"],
                    "params"        =>  $request["PARAMETERS"]
                ));

            }

        } else {

            foreach ($requests as $request) $return[$request["METHOD"]] = $request["PARAMETERS"];

        }
        

        return $return;

    }

    static private function composeJsonRequest($request) {

        $return = array(
            "jsonrpc"   =>  "2.0",
            "method"    =>  $request["METHOD"],
            "params"    =>  $request["PARAMETERS"]
        );

        if ( $request["ID"] === true ) {

            $return["id"] = mt_rand();

            $id = $return["id"];

        }

        else if ( is_int($request["ID"]) ) {

            $return["id"] = $request["ID"];

            $id = $return["id"];

        }

        else $id = null;

        return array($return, $id);

    }
    
}
