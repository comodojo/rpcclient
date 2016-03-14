<?php namespace Comodojo\RpcClient;

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

class RpcRequest {

    private $method;

    private $parameters = array();

    private $special_types = array();

    /**
     * Request's id
     *
     * @var boolean|integer
     */
    private $id = true;

    private $uid;

    public function __construct() {

        $this->uid = md5(uniqid(rand(), true));

    }

    public function setMethod($method) {

        if ( empty($method) || !is_string($method) ) throw new Exception("Invalid RPC method");

        $this->method = $method;

        return $this;

    }

    public function setParameters($params) {

        if ( !is_array($params) ) throw new Exception("Invalid RPC parameters");

        if ( !empty($params) ) $this->parameters = $params;

        return $this;

    }

    public function setSpecialType(&$value, $type) {

        $type = strtolower($type);

        if ( empty($value) || !in_array($type, array("base64", "datetime", "cdata")) ) {

            throw new Exception("Invalid value type");

        }

        $this->special_types[$value] = $type;

        return $this;

    }

    public function setId($id = null) {

        if ( is_null($id) || is_int($id) || is_bool($id) ) {

            $this->id = $id;

        } else {

            throw new Exception("Invalid RPC id");

        }

        return $this;

    }

    public function getMethod() {

        return $this->method;

    }

    public function getParameters() {

        return $this->parameters;

    }

    public function getSpecialTypes() {

        return $this->special_types;

    }

    public function getId() {

        return $this->id;

    }

    public function getUniqueId() {

        return $this->uid;

    }

    public function toArray() {

        return array(
            'uid' => $this->uid,
            'method' => $this->method,
            'parameters' => $this->parameters,
            'special_types' => $this->special_types,
            'id' => $this->id
        );

    }

    public static function create($method, $parameters = array(), $id = true) {

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
