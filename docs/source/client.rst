.. _client:

Using the client
================

.. _comodojo/httprequest: https://github.com/comodojo/httprequest
.. _comodojo/daemon: https://github.com/comodojo/daemon
.. _SocketTransport: https://github.com/comodojo/daemon/blob/master/src/Comodojo/Daemon/Socket/SocketTransport.php
.. _comodojo/rpcserver: https://github.com/comodojo/rpcserver

Once a request is composed, the ``\Comodojo\RpcClient\RpcClient`` class can be used to send it to the server and retrieve the response. It will seamlessly format the message according to selected protocol, send it to the server, read and decode the response.

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcClient\RpcClient;
    use \Comodojo\RpcClient\RpcRequest;

    $client = new RpcClient("http://phpxmlrpc.sourceforge.net/server.php");
    $client->addRequest( RpcRequest::create("echo", ['Hello Comodojo!']) );
    $result = $client->send();

Optionally, the client can be configured to use a PSR-3 compliant logger and/or a custom transport (second and third arguments, see below).

The autoclean switch
--------------------

By default, the client will hold one or more requests until the ``RpcClient::send`` method is invoked and it cleans the request's stack when a response is received.

This behaviour can be overridden using the ``RpcClient::setAutoclean`` method that will force the client to keep each request and reply it on next iteration.

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcClient\RpcClient;
    use \Comodojo\RpcClient\RpcRequest;

    $client = new RpcClient("http://phpxmlrpc.sourceforge.net/server.php");
    $client->setAutoclean(false);
    $client->addRequest( RpcRequest::create("echo", ['Hello Comodojo!']) );
    $result = $client->send();
    $client->addRequest( RpcRequest::create("echo", ['Hello Comodojo...2!']) );

    // client will send two requests to the server and result will consequently contain two responses.
    $result = $client->send();

Selecting RPC protocol
----------------------

To select XMLRPC (default) or JSONRPC protocols:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcClient\RpcClient;

    $client = new RpcClient("http://phpxmlrpc.sourceforge.net/server.php");
    $client->setProtocol(RpcClient::JSONRPC);
    // or
    // $client->setProtocol(RpcClient::XMLRPC);

Change default encoding
-----------------------

By default, client will encode requests in *utf-8*. To select a different encoding:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcClient\RpcClient;

    $client = new RpcClient("http://phpxmlrpc.sourceforge.net/server.php");
    $client->setEncoding('ISO-8859-2');

Transport
---------

The RpcClient comes with an embedded HTTP transport manager that makes use of `comodojo/httprequest`_ lightweight library.

If class is not inited with a custom transport, this one will be used.

To access transport instance, the ``RpcClient::getTransport`` method can be used before invoking ``RpcClient::send``.

Custom transport classes should implement the ``\Comodojo\RpcClient\Interfaces\Transport``. The `SocketTransport`_ of `comodojo/daemon`_ library is a good example to start with.

Encryption
----------

When used in combination with `comodojo/rpcserver`_, the RpcClient can be configured to seamlessly encrypt messages and decrypt reponses using a pre shared key.

To enable this feature, a key can be passed to ``RpcClient::getTransport`` method:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcClient\RpcClient;

    $client = new RpcClient("http://example.com/rpcserver");
    $client->setEncryption('this is my super secret key');
