"""
URLConf for Django user registration and authentication.

Recommended usage is a call to ``include()`` in your project's root
URLConf to include this URLConf for any URL beginning with
``/papershare/``.

"""

from django.conf.urls.defaults import *
from django.views.generic import list_detail
from models import Paper, Request

request_info = {
    "queryset" : Request.objects.all(),
    "template_object_name" : "request",
}

urlpatterns = patterns('',
    (r'^$', "ncs.papershare.views.homepage"),
    (r'^mine/$', "ncs.papershare.views.mypage"),
    (r'^request/$', "ncs.papershare.views.requestPaper"),
    (r'^list/$', list_detail.object_list, request_info)
)