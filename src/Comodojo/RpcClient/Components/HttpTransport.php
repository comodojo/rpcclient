<?php namespace Comodojo\RpcClient\Components;

use \Comodojo\RpcClient\Interfaces\Transport as TransportInterface;
use \Comodojo\Httprequest\Httprequest;
use \phpseclib\Crypt\AES;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\HttpException;
use \Comodojo\Exception\RpcException;
use \Exception;

class HttpTransport extends Httprequest implements TransportInterface {

    private $aes = null;

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
    public function performCall(LoggerInterface $logger, $data, $content_type, $encrypt=false) {

        $this->setHttpMethod("POST");

        try {

            $logger->notice("Sending RPC data");

            $logger->debug("Original request data dump: ".$data);

            $data = $this->can($data, $encrypt);

            $logger->debug("Real request data dump: ".$data);

            $response = $this->setContentType($content_type)->send($data);

            $logger->debug("Real response data dump: ".$response);

            $return = $this->uncan($response, $encrypt);

            $logger->debug("Decoded response data dump: ".$return);

        } catch (HttpException $he) {

            $logger->error("HTTP Transport error: ".$he->getMessage());

            throw $he;

        } catch (RpcException $re) {

            $logger->error("RPC Client error: ".$re->getMessage());

            throw $re;

        } catch (Exception $e) {

            $logger->critical("Generic Client error: ".$e->getMessage());

            throw $e;

        }

        return $return;

    }

    private function can($data, $key) {

        if ( !empty($key) && is_string($key) ) {

            $this->aes = new AES();

            $this->aes->setKey($key);

            $return = 'comodojo_encrypted_request-'.base64_encode( $this->aes->encrypt($data) );

        } else {

            $return = $data;

        }

        return $return;

    }

    private function uncan($data, $key) {

        if ( !empty($key) && is_string($key) ) {

            if ( self::checkEncryptedResponseConsistency($data) === false ) throw new RpcException("Inconsistent encrypted response received");

            $return = $this->aes->decrypt(base64_decode(substr($data, 28)));

        } else {

            $return = $data;

        }

        return $return;

    }

    /**
     * Check if an encrypted envelope is consisent or not
     *
     * @param   string    $data
     *
     * @return  bool
     */
    private static function checkEncryptedResponseConsistency($data) {

        return substr($data, 0, 27) == 'comodojo_encrypted_response' ? true : false;

    }

}
