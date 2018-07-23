<?php namespace Comodojo\RpcClient;

use \Comodojo\Foundation\Utils\UniqueId;
use \InvalidArgumentException;
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

class RpcRequest {

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $special_types = [];

    private static $supported_special_types = [
        "base64",
        "datetime",
        "cdata"
    ];

    /**
     * Request's id
     *
     * @var boolean|integer
     */
    private $id = true;

    /**
     * @var string
     */
    private $uid;

    public function __construct() {

        $this->uid = UniqueId::generate(32);

    }

    /**
     * Get request's unique id
     *
     * @return string
     */
    public function getUniqueId() {

        return $this->uid;

    }

    /**
     * Get rpc method
     *
     * @return string|null
     */
    public function getMethod() {

        return $this->method;

    }

    /**
     * Set rpc method
     *
     * @param string $method
     * @return self
     * @throws InvalidArgumentException
     */
    public function setMethod($method) {

        if ( empty($method) || !is_string($method) ) throw new InvalidArgumentException("Invalid RPC method");

        $this->method = $method;

        return $this;

    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParameters() {

        return $this->parameters;

    }

    /**
     * Set parameters
     *
     * @param array $params
     * @return self
     */
    public function setParameters(array $params = []) {

        $this->parameters = $params;

        return $this;

    }

    /**
     * Get values marked as special type
     *
     * @return array
     */
    public function getSpecialTypes() {

        return $this->special_types;

    }

    /**
     * Set values as special type
     *
     * @param string $value
     * @param string $type
     * @return self
     * @throws InvalidArgumentException
     */
    public function setSpecialType(&$value, $type) {

        $type = strtolower($type);

        if ( empty($value) || !in_array($type, self::$supported_special_types) ) {
            throw new InvalidArgumentException("Invalid value type");
        }

        $this->special_types[$value] = $type;

        return $this;

    }

    /**
     * Get request's id
     *
     * @return int|bool|null
     */
    public function getId() {

        return $this->id;

    }

    /**
     * Set request's id
     *
     * @param int|bool|null $id
     * @return self
     * @throws InvalidArgumentException
     */
    public function setId($id = null) {

        if ( is_null($id) || is_int($id) || is_bool($id) ) {

            $this->id = $id;

        } else {

            throw new InvalidArgumentException("Invalid RPC id");

        }

        return $this;

    }

    /**
     * Export request as an array
     *
     * @return array
     */
    public function toArray() {

        return [
            'uid' => $this->uid,
            'method' => $this->method,
            'parameters' => $this->parameters,
            'special_types' => $this->special_types,
            'id' => $this->id
        ];

    }

    /**
     * Static constructor
     *
     * @param string $method
     * @param array $parameters
     * @param int|bool|null $id
     * @return RpcRequest
     * @throws Exception
     */
    public static function create($method, array $parameters = [], $id = true) {

        $request = new RpcRequest();

        try {

            $request->setMethod($method)
                ->setParameters($parameters)
                ->setId($id);

        } catch (Exception $e) {

            throw $e;

        }

        return $request;

    }

}
