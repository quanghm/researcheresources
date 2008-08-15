from django.shortcuts import render_to_response
from django.http import HttpResponseRedirect, HttpResponse
import datetime
from ncs.papershare.models import Announcement
from django.contrib import auth
from django.template import RequestContext

def homepage(request, message = None):
    announcements = Announcement.objects.order_by('-date')
    context = {"announcements" : announcements}
    if message is not None:
        context["message"] = message
    return render_to_response('ncs/homepage.html', context)

def login(request):
    username = request.POST.get("username")
    password = request.POST.get("password")
    print " ---- ", username, password
    user = auth.authenticate(username=username, password=password)
    print " ++++ " , user
    if user is None:
        return homepage(request, message = "Invalid username/password")
    else:
        auth.login(request, user)
        return HttpResponseRedirect("/papershare/mine/")

def mypage(request):
    #check if user logged in
    if not request.user.is_authenticated():
        return HttpResponseRedirect("/papershare/")
    announcements = Announcement.objects.order_by('-date')
    context = {}
    
    context = RequestContext(request)
    context.update({
            "announcements" : announcements,
            "requested" : 0,
            "to_serve" : 0})
    return render_to_response('ncs/mypage.html', context)

