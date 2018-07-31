.. _requests:

Composing requests
==================

.. _xmlrpc specs: http://xmlrpc.scripting.com/spec.html
.. _jsonrpc specs: https://www.jsonrpc.org/specification

Each RPC request, regardless of the protocol, should be composed as an instance of the class ``\Comodojo\RpcClient\RpcRequest``.

Each request, once created, generate it's unique id that will never be transmitted to the server but is used to discriminate requests in case of batches.

For example, to invoke the ``echo`` method on the server side that may accept a parameter:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcClient\RpcRequest;

    $request = new RpcRequest;
    $request
        ->setMethod('echo')
        ->setParameters([
            "Hello world!"
        ]);

Or using the static ``RpcRequest::create`` constructor:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcClient\RpcRequest;

    $request = RpcRequest::create('echo', ["Hello world!"]);

Handling special types
----------------------

When using XML-RPC protocol, *base64*, *datetime* and *cdata* parameters must me explicitly declared using the ``RpcRequest::setSpecialType`` method, in order to produce a well formatted xml output.

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcClient\RpcRequest;

    $request = new RpcRequest;

    // base64 of: "I checked it very thoroughly", said the computer,
    //  "and that quite definitely is the answer. I think the problem,
    //  to be quite honest with you, is that you've never actually
    //  known what the question is."
    $parameters = [
        "IkkgY2hlY2tlZCBpdCB2ZXJ5IHRob3JvdWdob".
        "HkiLCBzYWlkIHRoZSBjb21wdXRlciwgImFuZC".
        "B0aGF0IHF1aXRlIGRlZmluaXRlbHkgaXMgdGh".
        "lIGFuc3dlci4gSSB0aGluayB0aGUgcHJvYmxl".
        "bSwgdG8gYmUgcXVpdGUgaG9uZXN0IHdpdGgge".
        "W91LCBpcyB0aGF0IHlvdSd2ZSBuZXZlciBhY3".
        "R1YWxseSBrbm93biB3aGF0IHRoZSBxdWVzdGl".
        "vbiBpcy4i"
    ];

    $request
        ->setMethod('echo')
        ->setParameters($parameters)
        ->setSpecialType($parameters[0], "base64");

About request id
----------------

When using a JSON-RPC protocol, each request should have it's own id, otherwise it is assumed to be a notification.

.. note:: Please see `jsonrpc specs`_ for more information about how request are structured and interpreted.

By default, this library will assign a random id to each request. The method ``RpcRequest::setId`` is availabe to override this behaviour:

- if id is set to true, the lib will generate a random id (default behaviour);
- if id is an integer or a string, it will be left intact;
- if id is set to null, the request will be treated as a notification.
