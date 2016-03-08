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

trait Encryption {

    /**
     * Enable comodojo encrypted transport
     *
     * @var mixed
     */
    private $encryption = false;

    /**
     * Set encryption key; this will enable the NOT-STANDARD payload encryption
     *
     * @param   string  $key Encryption key
     *
     * @return  \Comodojo\RpcClient\RpcClient
     *
     * @throws \Exception
     */
    public function setEncryption($key) {

        if ( empty($key) ) throw new Exception("Shared key cannot be empty");

        $this->$encryption = $key;

        return $this;

    }

    final public function getEncryption() {

        return $this->encryption;

    }

}
