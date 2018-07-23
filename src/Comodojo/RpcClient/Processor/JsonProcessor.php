<?php namespace Comodojo\RpcClient\Processor;

use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\RpcException;
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

class JsonProcessor extends AbstractProcessor {

    private $ids = [];

    private $is_multicall = false;

    /**
     * {@inheritdoc}
     */
    public function encode(array $requests) {

        $requests = array_values($requests);

        $this->is_multicall = sizeof($requests) > 1 ? true : false;

        $payload = [];

        foreach ($requests as $request) {

            list($composed, $rid) = self::composeJsonRequest($request);

            $payload[] = $composed;

            if ( $rid !== null ) $this->ids[] = $rid;

        }

        return sizeof($payload) > 1 ? json_encode($payload) : json_encode($payload[0]);

    }

    /**
     * {@inheritdoc}
     */
    public function decode($response) {

        try {

            if ( sizeof($this->ids) == 0 ) {

                return true;

            }

            $content = json_decode($response, true);

            if ( is_null($content) ) throw new Exception("Incomprehensible or empty response");

            if ( $this->is_multicall === false ) {

                if ( $content["id"] != $this->ids[0] ) throw new Exception("Invalid response ID received");

                $return = $content["result"];

            } else {

                $batch_content = [];

                foreach ( $this->ids as $key => $id ) {

                    if ( !isset($content[$key]) ) {
                        $batch_content[$key] = [
                            "error" => [
                                "code" => null,
                                "message" => "Empty response"
                            ]
                        ];
                    } else if ( isset($content[$key]["error"]) ) {
                        $batch_content[$key] = [
                            "error" => $content["error"]
                        ];
                    } else if ( !isset($content[$key]["id"]) ) {
                        $batch_content[$key] = [
                            "error" => [
                                "code" => null,
                                "message" => "Malformed response received"
                            ]
                        ];
                    } else if ( $content[$key]["id"] != $id ) {
                        $batch_content[$key] = [
                            "error" => [
                                "code" => null,
                                "message" => "Invalid response ID received"
                            ]
                        ];
                    } else {
                        $batch_content[$key] = [
                            "result" => $content[$key]["result"]
                        ];
                    }

                }

                $return = $batch_content;

            }

        } catch (Exception $xe) {

            throw $xe;

        }

        return $return;

    }

    private static function composeJsonRequest(RpcRequest $request) {

        $return = [
            "jsonrpc"   =>  "2.0",
            "method"    =>  $request->getMethod(),
            "params"    =>  $request->getParameters()
        ];

        $rid = $request->getId();

        if ( $rid === true ) {

            $id = $return["id"] = $request->getUniqueId();

        } else if ( is_scalar($rid) ) {

            $id = $return["id"] = $rid;

        } else {

            $id = null;

        }

        return [$return, $id];

    }

}
