Basic Usage
===========

Following a quick and dirty example of lib basic usage.

.. note:: For more detailed informations, please see :ref:`client` and :ref:`requests` pages.

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcClient\RpcClient;
    use \Comodojo\RpcClient\RpcRequest;
    use \Exception;

    try {

        // create a RpcClient instance (default XML)
        $client = new RpcClient( "http://phpxmlrpc.sourceforge.net/server.php" );

        // create and inject a request
        $client->addRequest( RpcRequest::create("echo", ['Hello Comodojo!']) );

        // send the request
        $result = $client->send();

    } catch (Exception $e) {

        /* something did not work :( */
        throw $e;

    }

    echo $result;
