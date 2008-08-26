#copied from http://www.djangosnippets.org/snippets/16/
import logging
from django.conf import settings

def getLogger():
    logger = logging.getLogger()
    hdlr = logging.FileHandler(settings.LOG_FILE)
    formatter = logging.Formatter('[%(asctime)s]%(levelname)-8s"%(message)s"','%Y-%m-%d %a %H:%M:%S') 
    
    hdlr.setFormatter(formatter)
    logger.addHandler(hdlr)
    logger.setLevel(logging.NOTSET)

    return logger

def debug(msg):
    logger = getlogger()
    logger.debug(msg)
    
