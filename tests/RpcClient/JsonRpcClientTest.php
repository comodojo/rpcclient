<?php

class JsonRpcClientTest extends \PHPUnit_Framework_TestCase {

    protected $rpch = null;

    public function setUp() {

        try {
            
            $this->rpch = new \Comodojo\RpcClient\RpcClient( "http://localhost" );

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

            $this->rpch->addRequest("echo", array($string));

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }
        
        $this->assertSame($string, $result);

    }

    public function testNotification() {

        try { 

            $this->rpch->addRequest("notify", array("comodojo"), null);

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }

        $this->assertTrue($result);

    }

    public function testAdd() {

        try { 

            $this->rpch->addRequest("add", array(40,2));

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }
        
        $this->assertSame(42, $result);

    }

    public function testMulticall() {

        $string = "comodojo";

        try { 

            $this->rpch
                ->addRequest("add", array(40,2))
                ->addRequest("echo", array($string)
            );

            $result = $this->rpch->send();

        } catch (\Exception $e) { throw $e; }
        
        $this->assertSame(42, $result[0]['result']);
        $this->assertSame($string, $result[1]['result']);

    }

}