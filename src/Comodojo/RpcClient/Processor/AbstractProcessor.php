<?php namespace Comodojo\RpcClient\Processor;

use \Comodojo\RpcClient\Interfaces\Processor as ProcessorInterface;
use \Comodojo\RpcClient\Traits\Encoding as EncodingTrait;
use \Psr\Log\LoggerInterface;

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

abstract class AbstractProcessor implements ProcessorInterface {

    use EncodingTrait;

    protected $logger;

    /**
     * {@inheritdoc}
     */
    public function __construct($encoding, LoggerInterface $logger) {

        $this->setEncoding($encoding);

        $this->logger = $logger;

    }

    /**
     * {@inheritdoc}
     */
    abstract public function encode(array $requests);

    /**
     * {@inheritdoc}
     */
    abstract public function decode($response);

}
