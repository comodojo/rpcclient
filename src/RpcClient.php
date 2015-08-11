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
 * It optionally supports a not standard encrypted transport
 * 
 * @package     Comodojo Spare Parts
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     MIT
 *
 * LICENSE:
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class RpcClient {
    
    /**
     * Default disabled: native funcs are faster but are not compatible with base64, datetime
     * and cdata values (as implemented here)
     *
     * @var bool
     */
    static private $useNativeEncoder = false;

    /**
     * Enable comodojo encrypted transport
     *
     * @var mixed
     */
    private $encrypt = false;
    
    /**
     * RPC protocol
     *
     * @var string
     */
    private $protocol = 'XML';
    
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

    // internals

    private $sender = null;

    private $requests = array();

    private $special_types = array();
    
    /**
     * Class constructor
     *
     * @param   string  $server  Remote RPC server address
     *
     * @throws \Comodojo\Exception\HttpException
     */
    public function __construct($server) {

        if ( empty($server) ) throw new Exception("Invalid RPC server address");

        try {

            $this->sender = new Httprequest($server);
            
            $this->sender->setHttpMethod("POST");

        }
        catch (HttpException $he) {

            throw $he;

        }

    }

    /**
     * Set RPC protocol
     *
     * @param   string  $protocol RPC protocol
     *
     * @return  \Comodojo\RpcClient\RpcClient
     * 
     * @throws \Exception
     */
    final public function setProtocol($protocol) {

        $proto = strtoupper($protocol);

        if ( !in_array($proto, $this->supported_protocols) ) throw new Exception("Invalid RPC protocol");

        $this->protocol = $proto;

        return $this;

    }

    /**
     * Set encryption key; this will enable the NOT-STANDARD payload encryption
     *
     * @param   string  $key Encryption key
     *
     * @return  \Comodojo\RpcClient\RpcClient
     * 
     * @throws \Exception
     */
    final public function setEncryption($key) {

        if ( empty($key) ) throw new Exception("Shared key cannot be empty");

        $this->encrypt = $key;

        return $this;

    }

    /**
     * Set encoding (default to utf-8)
     *
     * @param   string  $encoding Characters encoding
     *
     * @return  \Comodojo\RpcClient\RpcClient
     */
    final public function setEncoding($encoding) {

        $this->encoding = $encoding;

        return $this;

    }
    
    /**
     * Set the XML encoder
     * 
     * If true, the comodojo xmlrpc encoder will be used (default). Otherwise
     * message will be encoded using PHP XML-RPC Functions.
     * 
     * WARNING: using PHP XML-RPC Functions does not support special value
     * types support!
     *
     * @param   bool  $mode
     *
     * @return  \Comodojo\RpcClient\RpcClient
     */
    final public function setXmlEncoder($mode=true) {
        
        if ( $mode === false ) $this->useNativeEncoder = true;
        
        else $this->useNativeEncoder = false;
        
        return $this;
        
    }

    /**
     * Set special type for a given values (referenced)
     * 
     * @param   mixed  $value The given value (referenced)
     * @param   string $type  The value type (base64, datetime or cdata)
     *
     * @return  \Comodojo\RpcClient\RpcClient
     * 
     * @throws  \Exception
     */
    final public function setValueType(&$value, $type) {

        if ( empty($value) OR !in_array(strtolower($type), array("base64","datetime","cdata")) ) throw new Exception("Invalid value type");

        $this->special_types[$value] = strtolower($type);

        return $this;

    }

    /**
     * Add request in queue
     * 
     * @param   string  $method      RPC method
     * @param   array   $parameters  Request parameters
     * @param   mixed   $id          Id (only for JSON RPC)
     *
     * @return  \Comodojo\RpcClient\RpcClient
     * 
     * @throws  \Exception
     */
    final public function addRequest($method, $parameters=array(), $id=true) {

        if ( empty($method) OR !is_scalar($method) ) throw new Exception("Invalid method (not scalar or empty)");
        
        if ( !is_array($parameters) ) throw new Exception("Bad parameters (not array)");

        array_push($this->requests, array(
            "METHOD"    =>  $method,
            "PARAMETERS"=>  $parameters,
            "ID"        =>  $id
        ));

        return $this;

    }

    /**
     * Set autoclean on/off
     * 
     * @param   bool   $mode  If true, requests will be removed from queue at each send()
     *
     * @return  \Comodojo\RpcClient\RpcClient
     */
    final public function setAutoclean($mode=true) {

        $this->autoclean = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    /**
     * Get the transport layer
     * 
     * This method will return the Httprequest object in order to customize transport
     * options before sending request(s)
     * 
     * @return  \Comodojo\Httprequest\Httprequest
     */
    final public function getTransport() {
        
        return $this->sender;
        
    }

    /**
     * Send request(s) to server
     *
     * @return mixed
     * 
     * @throws \Comodojo\Exception\RpcException
     * @throws \Comodojo\Exception\HttpException
     * @throws \Comodojo\Exception\XmlrpcException
     * @throws \Exception
     */
    public function send() {

        if ( sizeof($this->requests) == 0 ) throw new Exception("Empty request, cannot perform call");

        try {

            switch ( $this->protocol ) {

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

    /**
     * Cleanup requests
     * 
     * @return  \Comodojo\RpcClient\RpcClient
     */
    final public function cleanRequests() {

        $this->requests = array();

        $this->special_types = array();

        return $this;

    }

    /**
     * Perform a json call
     *
     * @param   array   $request
     * 
     * @return  mixed
     *
     * @throws \Comodojo\Exception\RpcException
     * @throws \Comodojo\Exception\HttpException
     * @throws \Exception
     */
    private function jsonCall($request) {

        list($json_request, $id) = self::composeJsonRequest($request);

        try {
            
            $received = $this->performCall(json_encode($json_request), 'application/json');

            if ( $id !== null ) {

                $response = json_decode($received, true);

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

    /**
     * Perform a json multicall
     *
     * @param   array   $requests
     * 
     * @return  array
     *
     * @throws \Comodojo\Exception\RpcException
     * @throws \Comodojo\Exception\HttpException
     * @throws \Exception
     */
    private function jsonMulticall($requests) {

        $expected_ids = array();

        $batch_request = array();

        $batch_response = array();

        foreach ($requests as $request) {
            
            list($json_request, $id) = self::composeJsonRequest($request);

            if ( $id !== null ) $expected_ids[] = $id;

            $batch_request[] = $json_request;

        }

        try {
            
            $received = $this->performCall(json_encode($batch_request), 'application/json');

        } catch (HttpException $he) {

            throw $he;

        } catch (Exception $e) {

            throw $e;

        }

        if ( !empty($expected_ids) ) {

            $response = json_decode($received, true);

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
    private function xmlCall($request) {

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
            
            $received = $this->performCall($real_request, 'text/xml');

            if ( self::phpXmlrpcAvailable() ) {

                $decoded = xmlrpc_decode($received);

                if (is_array($decoded) && xmlrpc_is_fault($decoded)) throw new RpcException($decoded['faultString'], $decoded['faultCode']);

                $return = $decoded;

            } else {

                $decoder = new XmlrpcDecoder();

                $decoded = $decoder->decodeResponse($received);

                if ( $decoder->isFault() ) throw new RpcException($decoded['faultString'], $decoded['faultCode']);

                $return = $decoded;

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

            $received = $this->performCall($request, 'text/xml');

            if ( self::phpXmlrpcAvailable() ) {

                $decoded = xmlrpc_decode($received);

                if (is_array($decoded) && xmlrpc_is_fault($decoded)) throw new RpcException($decoded['faultString'], $decoded['faultCode']);

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

    /**
     * Send pre-econded request to server
     *
     * @param   string   $data
     * @param   string   $content_type
     * 
     * @return  string
     *
     * @throws \Comodojo\Exception\RpcException
     * @throws \Comodojo\Exception\HttpException
     */
    private function performCall($data, $content_type) {

        if ( $this->encrypt !== false ) {

            $aes = new Crypt_AES();

            $aes->setKey($this->encrypt);

            $data = 'comodojo_encrypted_request-'.base64_encode( $aes->encrypt($data) );

        }
    
        try {

            $response = $this->sender->setContentType($content_type)->send($data);

        }
        catch (HttpException $he) {

            throw $he;

        }

        if ( $this->encrypt !== false ) {
            
            if ( self::checkEncryptedResponseConsistency($response) === false ) throw new RpcException("Inconsistent encrypted response received");
        
            return $aes->decrypt( base64_decode( substr($response,28) ) );
           
        }

        else return $response;

    }

    /**
     * Check native encoder availability
     *
     * @return  bool
     */
    private static function phpXmlrpcAvailable() {

        return ( function_exists('xmlrpc_encode_request') AND self::$useNativeEncoder );

    }

    /**
     * Split multicall xml requests
     *
     * @param   array    $requests
     * 
     * @return  array
     */
    private static function splitMulticallXmlRequests($requests) {

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

    /**
     * Compose a json request
     *
     * @param   array    $requests
     * 
     * @return  array
     */
    private static function composeJsonRequest($request) {

        $return = array(
            "jsonrpc"   =>  "2.0",
            "method"    =>  $request["METHOD"],
            "params"    =>  $request["PARAMETERS"]
        );

        if ( $request["ID"] === true ) {

            $return["id"] = mt_rand();

            $id = $return["id"];

        }

        else if ( is_scalar($request["ID"]) ) {

            $return["id"] = $request["ID"];

            $id = $return["id"];

        }

        else $id = null;

        return array($return, $id);

    }
    
    /**
     * Check if an encrypted envelop is consisent or not
     *
     * @param   string    $data
     * 
     * @return  bool
     */
    private static function checkEncryptedResponseConsistency($data) {
        
        return substr($data,0,27) == 'comodojo_encrypted_response' ? true : false;
        
    }
    
}
