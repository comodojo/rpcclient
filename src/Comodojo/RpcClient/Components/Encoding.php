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

trait Encoding {

    /**
     * Characters encoding
     *
     * @var string
     */
    private $encoding = 'utf-8';

    /**
     * Set encoding (default to utf-8)
     *
     * @param   string  $encoding Characters encoding
     *
     * @return  \Comodojo\RpcClient\RpcClient
     */
    public function setEncoding($encoding) {

        $this->encoding = $encoding;

        return $this;

    }

    final public function getEncoding() {

        return $this->encoding;

    }

}
