"""
URLConf for Django user registration and authentication.

Recommended usage is a call to ``include()`` in your project's root
URLConf to include this URLConf for any URL beginning with
``/papershare/``.

"""

from django.conf.urls.defaults import *
from models import Paper, Request

urlpatterns = patterns('',
    (r'^$', "ncs.papershare.views.homepage"),
    (r'^mine/$', "ncs.papershare.views.mypage"),
    (r'^request/$', "ncs.papershare.views.requestPaper"),
    (r'^my_requests/(?P<page>[0-9]*)$', "ncs.papershare.views.listRequests"),
    (r'^supply/(?P<page>[0-9]*)$', "ncs.papershare.views.listRequestsToSupply"),
    (r'^public_pool/(?P<page>[0-9]*)$', "ncs.papershare.views.showPublicPool"),
    (r'^trash_pool/(?P<page>[0-9]*)$', "ncs.papershare.views.showTrashPool"),
    (r'^details/(?P<object_id>\d+)/$', "ncs.papershare.views.detailRequest"),
    (r'^upload/$', "ncs.papershare.views.uploadPaper"),
    (r'^feedback/$', "ncs.papershare.views.feedback"),
    (r'^contact/(?P<toUserId>\d+)/$', "ncs.papershare.views.contact"),
    (r'^contactPaper/(?P<requestId>\d+)/$', "ncs.papershare.views.contactPaper"),
    (r'^aboutus/$', "ncs.papershare.views.static", { 'template' : 'ncs/about.html'} ),
#    (r'^faq/$', "ncs.papershare.views.static", { 'template' : 'ncs/FAQ.html'} ),
)