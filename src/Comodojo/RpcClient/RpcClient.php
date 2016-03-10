<?php namespace Comodojo\RpcClient;

use \Comodojo\RpcClient\Processor\JsonProcessor;
use \Comodojo\RpcClient\Processor\XmlProcessor;
use \Comodojo\RpcClient\Components\Transport;
use \Comodojo\RpcClient\Components\Protocol;
use \Comodojo\RpcClient\Components\Encryption;
use \Comodojo\RpcClient\Components\Encoding;
use \Comodojo\RpcClient\Components\RequestManager;
use \Comodojo\RpcClient\Utils\NullLogger;
use \Comodojo\Exception\RpcException;
use \Comodojo\Exception\HttpException;
use \Comodojo\Exception\XmlrpcException;
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

    const JSONRPC = "JSON";

    const XMLRPC = "XML";

    // internals

    /**
     * Autoclean requests
     *
     * @var string
     */
    private $autoclean = true;

    private $transport;

    private $logger;

    private $request;

    /**
     * Class constructor
     *
     * @param   string  $server  Remote RPC server address
     *
     * @throws \Comodojo\Exception\HttpException
     */
    public function __construct($server, Logger $logger = null) {

        if ( empty($server) ) throw new Exception("Invalid RPC server address");

        $this->logger = is_null($logger) ? new NullLogger() : $logger;

        $this->request = new RequestManager();

        try {

            $this->transport = new Transport($server);

        } catch (HttpException $he) {

            throw $he;

        }

    }

    final public function logger() {

        return $this->logger;

    }

    final public function transport() {

        return $this->transport;

    }

    final public function request() {

        return $this->request;

    }

    /**
     * Set autoclean on/off
     *
     * @param   bool   $mode  If true, requests will be removed from queue at each send()
     *
     * @return  \Comodojo\RpcClient\RpcClient
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

        $requests = $this->request->get();

        if ( empty($requests) ) throw new Exception("No request to send");

        if ( $this->getProtocol() == self::XMLRPC ) {

            $processor = new XmlProcessor($this->getEncoding(), $this->logger());

            $content_type = "text/xml";

        } else {

            $processor = new JsonProcessor($this->getEncoding(), $this->logger());

            $content_type = "application/json";

        }

        try {

            $payload = $processor->encode($requests);

            $response = $this->transport()->performCall($this->logger, $payload, $content_type, $this->getEncryption());

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

        if ( $this->autoclean ) $this->request->clean();

        return $result;

    }

}
