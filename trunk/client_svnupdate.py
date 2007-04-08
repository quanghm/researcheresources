#!/usr/bin/env python

"""
A simple echo client
"""

import socket

host = 'cs-grads30.cs.uiuc.edu'
port = 50000
size = 1024
s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.connect((host,port))
s.send('VEF update')
data = s.recv(size)
s.close()
print 'Received:', data
