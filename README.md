## comodojo/rpcclient

An XML & JSON (2.0) RPC client with multicall support.

## Installation

Install [composer](https://getcomposer.org/), then:

`` composer require comodojo/rpcclient 1.0.* ``

## Basic usage

To start a new XML-RPC request simply create a new RpcClient instance providing remote host full address, add request(s) to queue and invoke the `send()` method:

```php
try {

	// create RpcClient instance
    $client = new \Comodojo\RpcClient\RpcClient( "www.example.org/xmlrpc/" );
    
    // add request to queue
    $client->addRequest( "my.method", array( "user"=>"john", "pass"=>"doe" ) );
    
    // fire client and get results
    $result = $client->send();

} catch (\Exception $e) {
	
	/* something did not work :( */

}

```

Same request using JSON-RPC protocol:

```php
try {

	// create RpcClient instance
    $client = new \Comodojo\RpcClient\RpcClient( "www.example.org/jsonrpc/" );
    
    // set JSON protocol
    $client->setProtocol("JSON");
    
    // add request to queue
    $client->addRequest( "my.method", array( "user"=>"john", "pass"=>"doe" ) );
    
    // fire client and get results
    $result = $client->send();

} catch (\Exception $e) {
	
	/* something did not work :( */

}

```

To raise a multicall request (no matter the protocol) just add more requests to queue:

```php
try {

	// create RpcClient instance
    $client = new \Comodojo\RpcClient\RpcClient( "www.example.org/xmlrpc/" );
    
    $client->addRequest( "my.method", array( "user"=>"john", "pass"=>"doe" ) )
           ->addRequest( "another.method", array( "test"=>true ) )
           ->addRequest( "last.method", array( "close"=>true, "value"=>42 ) );
    
    // fire client and get results
    $result = $client->send();

} catch (\Exception $e) {
	
	/* something did not work :( */

}

```

## Client options (chainable methods)

- Switch between XML and JSON protocol (default XML):

    ```php
        $client->setProtocol("JSON");
        
    ```

- Changing encoder characters encoding (default to utf-8):

    ```php
        $client->setEncoding("iso-8859-1");
        
    ```
    
- Use native XML encoder/decoder ([PHP XML-RPC functions](http://php.net/manual/en/ref.xmlrpc.php)) instead of [comodojo/xmlrpc](https://github.com/comodojo/xmlrpc) (this will broke support for special value types):

    ```php
        $client->setXmlEncoder(false);
        
    ```

- Set autoclean mode off (remove requests from queue at each `send`) - default on:

    ```php
        $client->setAutoclean(false);
        
    ```
    
- Use the NOT STANDARD encrypted transport (compatible with comodojo/rpcserver ONLY!):

    ```php
        $client->setEncryption("thisIsMyVeryLongEncryptionKey");
        
    ```

## Declaring special value type

Client supports base64, Iso8601 datetime and CDATA values, that should be declared before sending request.

To declare a value, use the `setValueType` method that will take parameter as a reference:

```php
try {

	// create RpcClient instance
    $client = new \Comodojo\RpcClient\RpcClient( "www.example.org/xmlrpc/" );
    
    $request_parameters = array( 
        "user"=>"john", 
        "pass"=>"doe", 
        "base_value"=>"SSBjaGVja2VkIGl0IHZlcnkgdGhvcm91Z2hseSwiIHNhaWQgdGhlIGNvbXB1dGVyLCAiYW5kIHRoYXQgcXVpdGUgZGVmaW5pdGVseSBpcyB0aGUgYW5zd2VyLiBJIHRoaW5rIHRoZSBwcm9ibGVtLCB0byBiZSBxdWl0ZSBob25lc3Qgd2l0aCB5b3UsIGlzIHRoYXQgeW91J3ZlIG5ldmVyIGFjdHVhbGx5IGtub3duIHdoYXQgdGhlIHF1ZXN0aW9uIGlzLg==" )
    
    // add request to queue
    $client->setValueType($request_parameters["base_value"], "base64")
           ->addRequest( "my.method", $request_parameters );
    
    // fire client and get results
    $result = $client->send();

} catch (\Exception $e) {
	
	/* something did not work :( */

}

```

## JSON-RPC id(s)

The `addRequest` method expects a third optional parameter (`$id`) to handle id of JSON-RPC request.

If it is set to `true` (default), client will automaticaly generate a random id for the request; if it is a scalar, value will be used as id. Other values will produce a `null` id (notification).

```php
// auto id
$client->addRequest( "my.method", array( "user"=>"john", "pass"=>"doe" ), true );

// predefined id
$client->addRequest( "my.method", array( "user"=>"john", "pass"=>"doe" ), 101 );

// no id (notification)
$client->addRequest( "my.method", array( "user"=>"john", "pass"=>"doe" ), null );

```

## HTTP transport options

In order to customize transport options (such as port, http protocol, ...), the `getTransport` method will return the current [Httprequest](https://github.com/comodojo/Httprequest) object.

For example, to change port and request timeout:

```php
try {

	// create RpcClient instance
    $client = new \Comodojo\RpcClient\RpcClient( "www.example.org/xmlrpc/" );
    
    // add request to queue
    $client->addRequest( "my.method", array( "user"=>"john", "pass"=>"doe" ) );
    
    // Set port to 8080 and timeout to 5 secs:
    $client->getTransport()->setPort(8080)->setTimeout(5);
    
    // fire client and get results
    $result = $client->send();

} catch (\Exception $e) {
	
	/* something did not work :( */

}

```
