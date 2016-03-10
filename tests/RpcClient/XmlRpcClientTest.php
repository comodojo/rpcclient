<?php

use \Comodojo\RpcClient\RpcClient;
use \Comodojo\RpcClient\RpcRequest;

class XmlRpcClientTest extends \PHPUnit_Framework_TestCase {

    protected $rpch = null;

    public function setUp() {

        try {

            $this->rpch = new RpcClient( "http://phpxmlrpc.sourceforge.net/server.php" );

            // This is useful to view transmission through an interception proxy like burp
            // $transport = $this->rpch->getTransport();
            // $transport->setProxy("http://127.0.0.1:8080");

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

            $request = RpcRequest::create("interopEchoTests.echoBase64", $echo);

            $request->setSpecialType($echo[0], "base64");

            $this->rpch->addRequest($request);

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }

        $this->assertSame($decoded, $result);

    }

    public function testInteropEchoTestsEchoDate() {

        $time = time();

        try {

            $request = RpcRequest::create("interopEchoTests.echoDate", array($time));

            $request->setSpecialType($time, "datetime");

            $this->rpch->addRequest($request);

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }

        $this->assertSame($time, $result);

    }

    public function testSystemMulticall() {

        $echoString_0 = "Hello comodojo!";
        $echoString_1 = array("Hello", "comodojo", "!");
        $echoString_2 = 42;
        $echoString_3 = array(42, 10, 100);
        $time = time();

        try {

            $this->rpch
                ->addRequest( RpcRequest::create("interopEchoTests.echoString", array($echoString_0)) )
                ->addRequest( RpcRequest::create("interopEchoTests.echoStringArray", array($echoString_1)) )
                ->addRequest( RpcRequest::create("interopEchoTests.echoInteger", array($echoString_2)) )
                ->addRequest( RpcRequest::create("interopEchoTests.echoIntegerArray", array($echoString_3)) )
                ->addRequest( RpcRequest::create("interopEchoTests.echoIntegerArray", array($echoString_0)) );

            $request = RpcRequest::create("interopEchoTests.echoDate", array($time));

            $request->setSpecialType($time, "datetime");

            $this->rpch->addRequest($request);

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }

        $this->assertSame($echoString_0, $result[0]);
        $this->assertSame($echoString_1, $result[1]);
        $this->assertSame($echoString_2, $result[2]);
        $this->assertSame($echoString_3, $result[3]);
        $this->assertSame(3, $result[4]['faultCode']);
        $this->assertSame($time, $result[5]);

    }

    private function commonRequests($method, $parameters=array()) {

        try {

            $request = RpcRequest::create($method, $parameters);

            $this->rpch->addRequest($request);

            $this->rpch->transport()->setTimeout(3);

            $result = $this->rpch->send();

        } catch (\Exception $e) {

            throw $e;

        }

        return $result;

    }

}
