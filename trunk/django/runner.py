import sys
import os

"""
let you run standalone scripts that import django library
example :
       python runner.py adsapi.recbyrelfb.poller
  or   python runner.py adsapi/recbyrelfb/poller
  or   python runner.py adsapi\recbyrelfb\poller.py
"""
def run(argv):
    if len(argv) < 2:
        print "Usage : python runner.py module_name"
        sys.exit(2)

    os.putenv("DJANGO_SETTINGS_MODULE","ncs.settings")

    module = argv[1]
    module = module.replace("\\",".").replace("/",".") #accept parent\child\child... form
    if module.endswith(".py") :
        module = module[:-3]
    exec("from %s import main" % module)
    main(argv)

if __name__ == "__main__":
    run(sys.argv)
