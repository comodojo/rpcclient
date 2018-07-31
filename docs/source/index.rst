comodojo/rpcserver documentation
================================

.. _comodojo/rpcserver: https://github.com/comodojo/rpcserver
.. _PSR-3: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
.. _XMLRPC: http://www.xmlrpc.com/spec
.. _JSONRPC: http://www.jsonrpc.org/specification

This library provides a framework (and transport) independent XML and JSON(2.0) RPC client.

Main features are:

- full `XMLRPC`_ and `JSONRPC`_ (2.0) protocols support, including multicall and batch requests
- `PSR-3`_ compliant logging
- configurable content encoding
- content encryption (if used in combination with `comodojo/rpcserver`_)

.. toctree::
   :maxdepth: 2
   :caption: Contents:

   install
   basicusage
   requests
   client
