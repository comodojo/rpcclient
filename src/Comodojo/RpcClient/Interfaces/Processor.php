<?php namespace Comodojo\RpcClient\Interfaces;

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

interface Processor {

    /**
     * Build the processor
     *
     * @param string $encodint
     * @param LoggerInterface $looger
     *
     * @throws Exception
     */
    public function __construct($encoding, LoggerInterface $logger);

    /**
     * Encode request(s)
     *
     * @param array $requests
     *
     * @return string
     *
     * @throws Exception
     */
    public function encode(array $requests);

    /**
     * Decode response
     *
     * @param string $response
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function decode($response);

}
