#!/usr/bin/env python

"""
A simple echo client
"""

import socket

host = 'cs-grad30.cs.uiuc.edu'
port = 8080
size = 1024
s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.connect((host,port))
s.send('VEF update')
data = s.recv(size)
s.close()
print 'Received:', data
