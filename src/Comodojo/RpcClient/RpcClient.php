<?php namespace Comodojo\RpcClient;

use \Comodojo\RpcClient\Interfaces\Transport as TransportInterface;
use \Comodojo\RpcClient\Interfaces\Processor as ProcessorInterface;
use \Comodojo\RpcClient\Traits\Protocol;
use \Comodojo\RpcClient\Traits\Encryption;
use \Comodojo\RpcClient\Traits\Encoding;
use \Comodojo\RpcClient\Processor\JsonProcessor;
use \Comodojo\RpcClient\Processor\XmlProcessor;
use \Comodojo\RpcClient\Components\HttpTransport;
use \Comodojo\RpcClient\Components\RequestManager;
use \Comodojo\Foundation\Logging\Manager as LogManager;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\RpcException;
use \Comodojo\Exception\HttpException;
use \Comodojo\Exception\XmlrpcException;
use \InvalidArgumentException;
use \Exception;

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

    use Protocol;
    use Encryption;
    use Encoding;
    use LoggerTrait;

    const JSONRPC = "JSON";

    const XMLRPC = "XML";

    /**
     * Autoclean requests
     *
     * @var string
     */
    private $autoclean = true;

    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * @var RequestManager
     */
    private $request;

    /**
     * @var JsonProcessor
     */
    private $json_processor;

    /**
     * @var XmlProcessor
     */
    private $xml_processor;

    /**
     * Class constructor
     *
     * @param string $server
     *  Remote RPC server address
     * @param LoggerInterface $logger
     *  Logger interface
     * @param TransportInterface $transport
     *  Transport Interface
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function __construct(
        $server,
        LoggerInterface $logger = null,
        TransportInterface $transport = null
    ) {

        if ( empty($server) ) throw new InvalidArgumentException("Invalid RPC server address");

        $this->setLogger(
            is_null($logger) ?
                LogManager::create('rpcclient', false)->getLogger() :
                $logger
            );

        $this->request = new RequestManager();

        $this->json_processor = new JsonProcessor($this->getEncoding(), $this->getLogger());

        $this->xml_processor = new XmlProcessor($this->getEncoding(), $this->getLogger());

        try {

            $this->transport = empty($transport) ? new HttpTransport($server) : $transport;

        } catch (Exception $e) {

            throw $e;

        }

    }

    public function getTransport() {

        return $this->transport;

    }

    public function getRequest() {

        return $this->request;

    }

    /**
     * Set autoclean on/off
     *
     * @param bool $mode
     *  If true, requests will be removed from queue at each send()
     *
     * @return self
     */
    public function setAutoclean($mode = true) {

        $this->autoclean = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    public function getAutoclean() {

        return $this->autoclean;

    }

    public function addRequest(RpcRequest $request) {

        $this->request->add($request);

        return $this;

    }

    public function getPayload(ProcessorInterface $processor = null) {

        $requests = $this->getRequest()->get();

        if ( empty($requests) ) throw new Exception("No request to send");

        $processor = is_null($processor) ? $this->getProcessor() : $processor;

        try {

            return $processor->encode($requests);

        } catch (Exception $e) {

            throw $e;

        }

    }

    /**
     * Send request(s) to server
     *
     * @return mixed
     *
     * @throws RpcException
     * @throws HttpException
     * @throws XmlrpcException
     * @throws Exception
     */
    public function send() {

        $protocol = $this->getProtocol();

        $content_type = $protocol == self::XMLRPC ? "text/xml" : "application/json";

        $processor = $this->getProcessor();

        try {

            $payload = $this->getPayload($processor);

            $response = $this->getTransport()
                ->performCall(
                    $this->logger,
                    $payload,
                    $content_type,
                    $this->getEncryption()
                );

            $result = $processor->decode($response);

        } catch (HttpException $he) {

            throw $he;

        } catch (RpcException $re) {

            throw $re;

        } catch (XmlrpcException $xe) {

            throw $xe;

        } catch (Exception $e) {

            throw $e;

        }

        if ( $this->getAutoclean() ) $this->getRequest()->clean();

        return $result;

    }

    private function getProcessor() {

        if ( $this->getProtocol() == self::XMLRPC ) {

            $processor = $this->xml_processor;

        } else {

            $processor = $this->json_processor;

        }

        return $processor->setEncoding($this->getEncoding());

    }

}
