<?php namespace Comodojo\RpcClient\Components;

use \Comodojo\RpcClient\RpcRequest;
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

trait Request {

    /**
     * Autoclean requests
     *
     * @var string
     */
    private $autoclean = true;

    private $requests = array();

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

    public function clean() {

        $this->requests = array();

        return $this;

    }

    public function addRequest(RpcRequest $request) {

        $uid = $request->getUniqueId();

        $this->requests[$uid] = $request;

        return $this;

    }

    public function getRequest($uid = null) {

        if ( is_null($uid) ) {

            return $this->requests;

        } else if ( array_key_exists($uid, $this->requests) ) {

            return $this->requests[$uid];

        } else {

            return null;

        }

    }

    public function deleteRequest($uid = null) {

        if ( is_null($uid) ) {

            $this->requests = arrray();

            return true;

        } else if ( array_key_exists($uid, $this->requests) ) {

            unset($this->requests[$uid]);

            return true;

        } else {

            return false;

        }

    }

}
