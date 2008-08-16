"""
URLConf for Django user registration and authentication.

Recommended usage is a call to ``include()`` in your project's root
URLConf to include this URLConf for any URL beginning with
``/testing/``.

"""

from django.conf.urls.defaults import *
from django.views.generic import list_detail

urlpatterns = patterns('',
    (r'^upload/$', "ncs.testing.views.upload_file"),
)
