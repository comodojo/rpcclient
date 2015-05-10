
import sys

SERVER_PORT = 28080

from SocketServer import ThreadingMixIn
from jsonrpclib.SimpleJSONRPCServer import SimpleJSONRPCServer

class SimpleThreadedJSONRPCServer(ThreadingMixIn, SimpleJSONRPCServer):
    pass

def notify(x):
    print x

def main():
    server = SimpleThreadedJSONRPCServer(('localhost', SERVER_PORT))
    server.register_function(notify)
    server.register_function(lambda x: x, 'echo')
    server.register_function(lambda x,y: x+y, 'add')
    server.serve_forever()

if __name__ == '__main__':
    main()
