#!/usr/bin/env python

"""
A simple echo server
"""

import socket
import commands as COM
import sys

def executeCommand():
    (status,output) = COM.getstatusoutput("svn up")
    return "Result : %s\n%s" % (status, output)
    
host = ''
port = 8080

if (len(sys.argv) == 2):
   port = int(sys.argv[1])
   print "Running on port", port

backlog = 5
size = 1024
s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.bind((host,port))
s.listen(backlog)
while 1:
    try:
        client, address = s.accept()
        data = client.recv(size)
        if data == "VEF update":
            print "Got command from ", address
	    message = executeCommand()
	    client.send(message)
            print "Message sent : ", message
        else:
	    print "Got rubbish from ", address, " : " , data
            client.send(data)
        client.close()
    except Exception,ex:
        print "Exception : ",ex
