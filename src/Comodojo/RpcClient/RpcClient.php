<?php namespace Comodojo\RpcClient;

use \Comodojo\RpcClient\Processor\JsonProcessor;
use \Comodojo\RpcClient\Processor\XmlProcessor;
use \Comodojo\RpcClient\Transport\Sender;
use \Comodojo\RpcClient\Components\Protocol;
use \Comodojo\RpcClient\Components\Encryption;
use \Comodojo\RpcClient\Components\Encoding;
use \Comodojo\RpcClient\Components\Request;
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
    use Request;

    const JSONRPC = "JSON";

    const XMLRPC = "XML";

    // internals

    private $sender;

    private $logger;

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

        try {

            $this->sender = new Sender($server, $this->logger);

        } catch (HttpException $he) {

            throw $he;

        }

    }

    public function logger() {

        return $this->logger;

    }

    public function sender() {

        return $this->sender;

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

        return $this->sender->transport();

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

        $requests = $this->getRequest();

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

            $response = $this->sender()->setContentType($content_type)->performCall($payload, $this->getEncryption());

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

        if ( $this->autoclean ) $this->clean();

        return $result;

    }

}
