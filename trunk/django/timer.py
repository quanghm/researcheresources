import sys
import os
import time

"""
let you run standalone scripts that import django library
example :
       python runner.py adsapi.recbyrelfb.poller
  or   python runner.py adsapi/recbyrelfb/poller
  or   python runner.py adsapi\recbyrelfb\poller.py
"""
def run(argv):
    if len(argv) < 2:
        print "Usage : python timer.py seconds 'command to run' "
        sys.exit(2)

    timeout = int(argv[1])
    command = argv[2]
    while True:
       os.system(command)
       print "Sleeping..."
       time.sleep(timeout)

if __name__ == "__main__":
    run(sys.argv)
