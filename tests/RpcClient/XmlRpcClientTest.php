<?php

class XmlRpcClientTest extends \PHPUnit_Framework_TestCase {

    protected $rpch = null;

    public function setUp() {

        try {
            
            $this->rpch = new \Comodojo\RpcClient\RpcClient( "http://phpxmlrpc.sourceforge.net/server.php" );

            $transport = $this->rpch->getTransport();

            // This is useful to view transmission through an interception proxy like burp
            //$transport->setProxy("http://127.0.0.1:8080");

        } catch (\Exception $e) {
            
            throw $e;

        }

    }

    public function testSystemGetCapabilities() {
        
        try { $result = $this->commonRequests("system.getCapabilities"); }

        catch (\Exception $e) { throw $e; }

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey("xmlrpc", $result);
        $this->assertArrayHasKey("system.multicall", $result);
        $this->assertArrayHasKey("introspection", $result);
        $this->assertArrayHasKey("json-rpc", $result);

    }

    public function testInteropEchoTestsEchoString() {

        $echoString = "Hello comodojo!";

        try { $result = $this->commonRequests("interopEchoTests.echoString", array($echoString)); }

        catch (\Exception $e) { throw $e; }
        
        $this->assertSame($echoString, $result);

    }

    public function testInteropEchoTestsEchoStringArray() {

        $echoString = array("Hello", "comodojo", "!");

        try { $result = $this->commonRequests("interopEchoTests.echoStringArray", array($echoString)); }

        catch (\Exception $e) { throw $e; }
        
        $this->assertSame($echoString, $result);

    }

    public function testInteropEchoTestsEchoInteger() {

        $echoString = 42;

        try { $result = $this->commonRequests("interopEchoTests.echoInteger", array($echoString)); }

        catch (\Exception $e) { throw $e; }
        
        $this->assertSame($echoString, $result);

    }

    public function testInteropEchoTestsEchoIntegerArray() {

        $echoString = array(42, 10, 100);

        try { $result = $this->commonRequests("interopEchoTests.echoIntegerArray", array($echoString)); }

        catch (\Exception $e) { throw $e; }
        
        $this->assertSame($echoString, $result);

    }

    public function testInteropEchoTestsEchoFloat() {

        $echoString = 122.34343;

        try { $result = $this->commonRequests("interopEchoTests.echoFloat", array($echoString)); }

        catch (\Exception $e) { throw $e; }
        
        $this->assertSame($echoString, $result);

    }

    public function testInteropEchoTestsEchoFloatArray() {

        $echoString = array(122.34343, 122.34343, 122.34343);

        try { $result = $this->commonRequests("interopEchoTests.echoFloatArray", array($echoString)); }

        catch (\Exception $e) { throw $e; }
        
        $this->assertSame($echoString, $result);

    }

    public function testInteropEchoTestsEchoStruct() {

        $echoString = array("say"=>"hello","to"=>"comodojo");

        try { $result = $this->commonRequests("interopEchoTests.echoStruct", array($echoString)); }

        catch (\Exception $e) { throw $e; }
        
        $this->assertSame($echoString, $result);

    }

    public function testInteropEchoTestsEchoStructArray() {

        $echoString = array(
            array("say"=>"hello","to"=>"comodojo"),
            array("say"=>"hello","to"=>"comodojo"),
            array("say"=>"hello","to"=>"comodojo")
        );

        try { $result = $this->commonRequests("interopEchoTests.echoStructArray", array($echoString)); }

        catch (\Exception $e) { throw $e; }
        
        $this->assertSame($echoString, $result);

    }

    public function testInteropEchoTestsEchoValue() {

        $echoString = array("test", "say"=>"hello", "to"=>"comodojo", "query", 42);

        try { $result = $this->commonRequests("interopEchoTests.echoValue", array($echoString)); }

        catch (\Exception $e) { throw $e; }
        
        $this->assertSame($echoString, $result);

    }

    public function testInteropEchoTestsEchoBase64() {

        $echo = array("IkkgY2hlY2tlZCBpdCB2ZXJ5IHRob3JvdWdobHkiLCBzYWlkIHRoZSBjb21wdXRlciwgImFuZCB0aGF0IHF1aXRlIGRlZmluaXRlbHkgaXMgdGhlIGFuc3dlci4gSSB0aGluayB0aGUgcHJvYmxlbSwgdG8gYmUgcXVpdGUgaG9uZXN0IHdpdGggeW91LCBpcyB0aGF0IHlvdSd2ZSBuZXZlciBhY3R1YWxseSBrbm93biB3aGF0IHRoZSBxdWVzdGlvbiBpcy4i");
    
        $decoded = "\"I checked it very thoroughly\", said the computer, \"and that quite definitely is the answer. I think the problem, to be quite honest with you, is that you've never actually known what the question is.\"";

        try { 

            $this->rpch->setValueType($echo[0], "base64")->addRequest("interopEchoTests.echoBase64", $echo);

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }
        
        $this->assertSame($decoded, $result);

    }

    public function testInteropEchoTestsEchoDate() {

        $time = time();

        try { 

            $this->rpch->setValueType($time, "datetime")->addRequest("interopEchoTests.echoDate", array($time));

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }
        
        $this->assertSame($time, $result);

    }

    public function testSystemMulticall() {

        $echoString_0 = "Hello comodojo!";
        $echoString_1 = array("Hello", "comodojo", "!");
        $echoString_2 = 42;
        $echoString_3 = array(42, 10, 100);

        try { 

            $this->rpch
                ->addRequest("interopEchoTests.echoString", array($echoString_0))
                ->addRequest("interopEchoTests.echoStringArray", array($echoString_1))
                ->addRequest("interopEchoTests.echoInteger", array($echoString_2))
                ->addRequest("interopEchoTests.echoIntegerArray", array($echoString_3));

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }
        
        $this->assertSame($echoString_0, $result[0][0]);
        $this->assertSame($echoString_1, $result[1][0]);
        $this->assertSame($echoString_2, $result[2][0]);
        $this->assertSame($echoString_3, $result[3][0]);

    }

    private function commonRequests($method, $parameters=array()) {

        try {

            $this->rpch->addRequest( $method, $parameters);

            $result = $this->rpch->send();

        } catch (\Exception $e) {
            
            throw $e;

        }

        return $result;

    }

}