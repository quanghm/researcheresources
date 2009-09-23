from django.conf.urls.defaults import *
from django.contrib.auth.views import login, logout,password_reset
from django.conf import settings

# Uncomment the next two lines to enable the admin:
from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
    # Example:
    # (r'^ncs/', include('ncs.foo.urls')),

    # Uncomment the next line to enable admin documentation:
    (r'^papershare/admin/doc/', include('django.contrib.admindocs.urls')),
    # Uncomment the next line for to enable the admin:
    (r'^papershare/admin/(.*)', admin.site.root),
    (r'^papershare/accounts/', include('ncs.registration.urls')),
    (r'^papershare/', include('ncs.papershare.urls')),
    (r'^testing/', include('ncs.testing.urls')),
    #see how to deal with static files
    #http://oebfare.com/blog/2007/dec/31/django-and-static-files/
    (r'^static/(?P<path>.*)$', 'django.views.static.serve', {'document_root': settings.MEDIA_ROOT}),

)
