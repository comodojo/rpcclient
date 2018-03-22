<?php namespace Comodojo\RpcClient\Components;

use \Exception;

/**
 * Protocol Trait
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

trait Protocol {

    /**
     * Supported RPC protocols
     *
     * @var string
     */
    protected $supported_protocols = array("XML", "JSON");

    /**
     * RPC protocol
     *
     * @var string
     */
    private $protocol = 'XML';

    /**
     * Set RPC protocol
     *
     * @param   string  $protocol RPC protocol
     *
     * @return  \Comodojo\RpcClient\RpcClient
     *
     * @throws \Exception
     */
    public function setProtocol($protocol) {

        $proto = strtoupper($protocol);

        if ( !in_array($proto, $this->supported_protocols) ) throw new Exception("Invalid RPC protocol");

        $this->protocol = $proto;

        return $this;

    }

    final public function getProtocol() {

        return $this->protocol;

    }

}
