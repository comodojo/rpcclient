<?php namespace Comodojo\RpcClient\Components;

use \Comodojo\RpcClient\RpcRequest;
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

class RequestManager {

    private $requests = [];

    public function clean() {

        $this->requests = [];

        return $this;

    }

    public function add(RpcRequest $request) {

        $this->requests[] = $request;

        return $this;

    }

    public function get($uid = null) {

        if ( is_null($uid) ) {

            return $this->requests;

        } else if ( $key = $this->searchByUid($uid) != null ) {

            return $this->requests[$key];

        } else {

            return null;

        }

    }

    public function delete($uid = null) {

        if ( is_null($uid) ) {

            $this->requests = arrray();

            return true;

        } else if ( $key = $this->searchByUid($uid) != null ) {

            unset($this->requests[$key]);

            return true;

        } else {

            return false;

        }

    }

    private function searchByUid($uid) {

        $element = array_filter(
            $this->requests,
            function ($e) use ($uid) {
                return $e->getUid() == $uid;
            }
        );

        return sizeof($element) == 1 ? array_keys($element)[0] : null;

    }

}
