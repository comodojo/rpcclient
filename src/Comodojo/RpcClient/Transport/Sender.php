<?php namespace Comodojo\RpcClient\Transport;

use \Psr\Log\LoggerInterface;
use \Comodojo\Httprequest\Httprequest;
use \Crypt_AES;
use \Comodojo\Exception\HttpException;
use \Comodojo\Exception\RpcException;
use \Exception;

class Sender {

    private $transport = null;

    private $logger = null;

    private $aes = null;

    public function __construct($server, LoggerInterface $logger) {

        $this->logger = $logger;

        try {

            $this->transport = new Httprequest($server);

            $this->transport->setHttpMethod("POST");

        } catch (HttpException $he) {

            throw $he;

        } catch (Exception $e) {

            throw $e;

        }

    }

    public function transport() {

        return $this->transport;

    }

    public function logger() {

        return $this->logger();

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
    public function performCall($data, $content_type, $encrypt=false) {

        try {

            $this->logger->notice("Sending data to ".$this->transport);

            $this->logger->debug("Original request data dump ", $data);

            $data = $this->can($data, $encrypt);

            $this->logger->debug("Real request data dump ", $data);

            $response = $this->transport->setContentType($content_type)->send($data);

            $this->logger->debug("Real response data dump ", $response);

            $return = $this->uncan($response, $encrypt);

            $this->logger->debug("Decoded response data dump ", $return);

        } catch (HttpException $he) {

            $this->logger->error("HTTP Transport error: ".$he->getMessage());

            throw $he;

        } catch (RpcException $re) {

            $this->logger->error("RPC Client error: ".$re->getMessage());

            throw $re;

        } catch (Exception $e) {

            $this->logger->critical("Generic Client error: ".$e->getMessage());

            throw $e;

        }

        return $return;

    }

    private function can($data, $key) {

        if ( !empty($key) && is_string($key) ) {

            $this->aes = new Crypt_AES();

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

            $return = $this->aes->decrypt(base64_decode(substr($response, 28)));

        } else {

            $return = $data;

        }

        return $return;

    }

    /**
     * Check if an encrypted envelop is consisent or not
     *
     * @param   string    $data
     *
     * @return  bool
     */
    private static function checkEncryptedResponseConsistency($data) {

        return substr($data, 0, 27) == 'comodojo_encrypted_response' ? true : false;

    }

}
