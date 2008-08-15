from django.shortcuts import render_to_response
from django.http import HttpResponseRedirect, HttpResponse
from django.contrib import auth
from django.template import RequestContext

from models import Announcement
from forms import PaperRequestForm

import datetime

def homepage(request):
    #if user is logged in, redirect to mypage
    if request.user.is_authenticated():
        return HttpResponseRedirect("/papershare/mine/")
    
    announcements = Announcement.objects.order_by('-date')
    context = {"announcements" : announcements}
    return render_to_response('ncs/homepage.html', context)

def mypage(request):
    #check if user logged in
    if not request.user.is_authenticated():
        return HttpResponseRedirect("/papershare/")
    
    announcements = Announcement.objects.order_by('-date')
    context = RequestContext(request)
    context.update({
            "announcements" : announcements,
            "requested" : 0,
            "to_serve" : 0})
    return render_to_response('ncs/mypage.html', context)

def requestPaper(request, form_class = PaperRequestForm):
    if not request.user.is_authenticated():
        return HttpResponseRedirect("/papershare/")
    
    if request.method == 'POST':
        form = form_class(data=request.POST, files=request.FILES)
        if form.is_valid():
            new_request = form.save()
            # success_url needs to be dynamically generated here; setting a
            # a default value using reverse() will cause circular-import
            # problems with the default URLConf for this application, which
            # imports this file.
            context = RequestContext(request)
            context.update({ "request" : new_request })
            return render_to_response("ncs/paper_request_complete.html", 
                                      context)
                                      
    else:
        form = form_class()
    
    context = RequestContext(request)
    context.update({
            "requested" : 0,
            "to_serve" : 0})
    return render_to_response("ncs/paper_request.html",
                              { 'form': form },
                              context_instance=context)