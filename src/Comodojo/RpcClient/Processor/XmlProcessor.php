<?php namespace Comodojo\RpcClient\Processor;

use \Psr\Log\LoggerInterface;
use \Comodojo\RpcClient\RpcRequest;
use \Comodojo\Xmlrpc\XmlrpcEncoder;
use \Comodojo\Xmlrpc\XmlrpcDecoder;
use \Comodojo\Exception\RpcException;
use \Comodojo\Exception\XmlrpcException;
use \Exception;

/**
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

class XmlProcessor extends AbstractProcessor {

    private $encoder;

    private $decoder;

    private $isMulticall = false;

    /**
     * {@inheritdoc}
     */
    public function __construct($encoding, LoggerInterface $logger) {

        parent::__construct($encoding, $logger);

        $this->encoder = new XmlrpcEncoder();

        $this->decoder = new XmlrpcDecoder();

    }

    /**
     * {@inheritdoc}
     */
    public function encode(array $requests) {

        $requests = array_values($requests);

        $this->isMulticall = sizeof($requests) > 1 ? true : false;

        try {

            $payload = $this->isMulticall ? $this->encodeMulticall($requests) : $this->encodeSingleCall($requests[0]);

        } catch (XmlrpcException $xe) {

            throw $xe;

        }

        return $payload;

    }

    /**
     * {@inheritdoc}
     */
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

        $this->logger->debug("Performing a single XML call");

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
     * @param array $requests
     * @return array
     * @throws XmlrpcException
     */
    private function encodeMulticall(array $requests) {

        $composed_requests = [];

    	$this->logger->debug("Performing an XML multicall");

    	$this->logger->debug("Data dump before encoding", $requests);

        foreach ($requests as $request) {

            $composed_requests[] = [
                $request->getMethod(),
                $request->getParameters()
            ];

            foreach ( $request->getSpecialTypes() as $key => $value ) {
                $this->encoder->setValueType($key, $value);
            }

        }

        try {

            $encoded_requests = $this->encoder
                ->setEncoding($this->getEncoding())
                ->encodeMulticall($composed_requests);

        } catch (XmlrpcException $xe) {

            throw $xe;

        }

        $this->logger->debug("Data dump after encoding: ".$encoded_requests);

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
