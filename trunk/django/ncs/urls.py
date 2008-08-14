from django.conf.urls.defaults import *
from django.contrib.auth.views import login, logout,password_reset

# Uncomment the next two lines to enable the admin:
from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
    # Example:
    # (r'^ncs/', include('ncs.foo.urls')),

    # Uncomment the next line to enable admin documentation:
    (r'^admin/doc/', include('django.contrib.admindocs.urls')),
    # Uncomment the next line for to enable the admin:
    (r'^admin/(.*)', admin.site.root),
    (r'^accounts/', include('ncs.registration.urls')),
    (r'^papershare/$', "ncs.papershare.views.homepage"),
    (r'^papershare/mine/$', "ncs.papershare.views.mypage"),
)
