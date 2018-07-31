<?php

use \Comodojo\RpcClient\RpcClient;
use \Comodojo\RpcClient\RpcRequest;

/**
 * @group JSONRPC
 */
class JsonRpcClientTest extends \PHPUnit_Framework_TestCase {

    protected $rpch = null;

    public function setUp() {

        try {

            $this->rpch = new RpcClient( "http://localhost" );

            $this->rpch->setProtocol("JSON");

            $transport = $this->rpch->getTransport();

            $transport->setPort(28080);

            // This is useful to view transmission through an interception proxy like burp
            //$transport->setProxy("http://127.0.0.1:8080");

        } catch (\Exception $e) {

            throw $e;

        }

    }

    public function testEcho() {

        $string = "comodojo";

        try {

            $this->rpch->addRequest( RpcRequest::create("echo", array($string)) );

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }

        $this->assertSame($string, $result);

    }

    public function testNotification() {

        try {

            $this->rpch->addRequest( RpcRequest::create("notify", array("comodojo"), null) );

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }

        $this->assertTrue($result);

    }

    public function testAdd() {

        try {

            $this->rpch->addRequest( RpcRequest::create("add", array(40,2)) );

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }

        $this->assertSame(42, $result);

    }

    public function testMulticall() {

        $string = "comodojo";

        try {

            $this->rpch
                ->addRequest( RpcRequest::create("add", array(40,2)) )
                ->addRequest( RpcRequest::create("echo", array($string) ));

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }

        $this->assertSame(42, $result[0]['result']);
        $this->assertSame($string, $result[1]['result']);

    }

    /**
     * @dataProvider idProvider
     */
    public function testCustomId($id) {

        $string = 'comodojo';

        try {

            $this->rpch->addRequest( RpcRequest::create("echo", [$string], $id) );

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }

        $this->assertSame($string, $result);

    }

    public function idProvider() {
        return [
            [100],
            ['101'],
            ['customid'],
            [true]
        ];
    }

}
