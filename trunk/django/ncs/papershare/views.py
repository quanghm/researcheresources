#!/usr/bin/env python
# -*- coding: UTF-8 -*-

from django.shortcuts import render_to_response
from django.http import HttpResponseRedirect, HttpResponse
from django.contrib.auth.models import User
from django.template import RequestContext
from django.views.generic import list_detail
from django.contrib.auth.decorators import login_required

from models import Announcement, Request
from forms import PaperRequestForm, PaperUploadForm, FeedbackForm
from ncs.settings import MEDIA_ROOT, MEDIA_URL
from ncs.utils.sendmail import sendmailFromTemplate

import datetime
import os
import re
from tempfile import mkstemp


def homepage(request):
    #if user is logged in, redirect to mypage
    if request.user.is_authenticated():
        return HttpResponseRedirect("/papershare/mine/")
    
    announcements = Announcement.objects.order_by('-date')
    context = {"announcements" : announcements}
    return render_to_response('ncs/homepage.html', context)

def getCommonContext(request):
    context = RequestContext(request)
    if request.user.is_authenticated():
        context.update(get_my_stats(request.user))
    return context
    
def get_my_stats(user):
    return {"requested" : Request.objects.filter(requester=user, status__lt=3).count(),
            "to_serve" : Request.objects.filter(supplier=user, status__lt=3).count()}
            
def mypage(request):
    #check if user logged in
    if not request.user.is_authenticated():
        return HttpResponseRedirect("/papershare/")
    
    announcements = Announcement.objects.order_by('-date')
    context = RequestContext(request)
    context.update({
            "announcements" : announcements
            }).update(get_my_stats(request.user.id))
    return render_to_response('ncs/mypage.html', context)

@login_required
def requestPaper(request, form_class = PaperRequestForm):
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
            return render_to_response("papershare/paper_request_complete.html", 
                                      context)
                                      
    else:
        form = form_class()
    
    context = RequestContext(request)
    context.update(get_my_stats(request.user))
    return render_to_response("papershare/paper_request.html",
                              { 'form': form },
                              context_instance=context)
    
@login_required
def listRequests(request,page=1):
    if not request.user.is_authenticated():
        return HttpResponseRedirect("/papershare/")
    queryset = Request.objects.filter(requester__exact=request.user, status__lt=3)
    template_object_name = "request"
    extra_context = get_my_stats(request.user)
    
    return list_detail.object_list(request, queryset=queryset, 
                                   template_object_name = template_object_name , 
                                   extra_context = extra_context,
                                   paginate_by = 10,
                                   page = page)
@login_required
def listRequestsToSupply(request,page=1):
    queryset = Request.objects.filter(supplier__exact=request.user, status__lt=3)
    template_object_name = "request"
    extra_context = get_my_stats(request.user)
    
    return list_detail.object_list(request, queryset=queryset, 
                                   template_object_name = template_object_name , 
                                   extra_context = extra_context,
                                   paginate_by = 10,
                                   page = page)

@login_required
def showPublicPool(request,page=1):
    my_research_field = User.objects.get(pk = request.user.id).get_profile().research_field
    queryset = Request.objects.filter(status__lt=3, paper__research_field__exact = my_research_field)
    template_object_name = "request"
    extra_context = get_my_stats(request.user)
    
    return list_detail.object_list(request, queryset=queryset, 
                                   template_object_name = template_object_name , 
                                   extra_context = extra_context,
                                   paginate_by = 10,
                                   page = page)


@login_required    
def detailRequest(request, object_id):
    queryset = Request.objects.all()
    template_object_name = "request"
    extra_context = get_my_stats(request.user)
    extra_context.update({'form': PaperUploadForm()})
    return list_detail.object_detail(request, object_id=object_id, 
                                     queryset=queryset, 
                                     template_name = "papershare/request_detail.html", 
                                     template_object_name = template_object_name , 
                                     extra_context = extra_context )

@login_required
def uploadPaper(request):
    #handle form submit
    if request.method == 'POST' :
        form = PaperUploadForm(request.POST, request.FILES)
        context = RequestContext(request)
        context.update(get_my_stats(request.user))
        if request.POST.get("buttonSupply") is not None:
            if form.is_valid():
                uploaded_url = handle_uploaded_file(request.FILES['file'])   
                form.save(uploaded_url)
                paperRequest = Request.objects.get(id=form.cleaned_data['request_id'])
                context.update({'uploaded_url' : uploaded_url,
                                'request' : paperRequest})
                sendmailFromTemplate(toAddr=paperRequest.requester.email,
                                     subject=u"Good news ! your paper request has been processed",
                                     template_name="papershare/request_processed_email.html",
                                     context=context)            
                return render_to_response('papershare/upload_complete.html',context)
            else:
                context.update({'form':form})
                return render_to_response('papershare/upload_error.html',context)
        
        elif request.POST.get("buttonPass") is not None: #pass to other supplier
            requestId = request.POST.get('request_id')
            if requestId is not None and requestId.isdigit():
                paperRequest = Request.objects.get(id=int(requestId))
                paperRequest.supplier = None
                paperRequest.status = 0 #pending. 
                paperRequest.save()
                message = u"Yêu cầu %d đã được chuyển cho người khác, tuy nhiên bạn vẫn có thể vào public pool để cung cấp nếu muốn" % paperRequest.id
                context.update({'message' : message}) 
                return render_to_response('ncs/simple_message.html', context)
        elif request.POST.get("buttonFail") is not None: #report fail by admin
            requestId = request.POST.get('request_id')
            if requestId is not None and requestId.isdigit():
                paperRequest = Request.objects.get(id=int(requestId))
                paperRequest.supplier = None
                paperRequest.status = 5 #failed.
                paperRequest.save()
                message = u"Yêu cầu %d da duoc chuyen vao trash pool" % paperRequest.id
                context.update({'message' : message}) 
                return render_to_response('ncs/simple_message.html', context)
    return HttpResponseRedirect("/papershare/")

def handle_uploaded_file(f):
    #see tempfile note
    #http://utcc.utoronto.ca/~cks/space/blog/python/UsingTempfile
    fd , fileName = mkstemp(f.name,"uploaded/",MEDIA_ROOT)
    
    destination = os.fdopen(fd, "w+b")
    for chunk in f.chunks():
        destination.write(chunk)
    destination.close()
    relFileName = fileName[len(MEDIA_ROOT)+1:]
    relFileName = re.sub("\\\\", "/", relFileName)
    return MEDIA_URL + relFileName

def feedback(request):
    if request.method == 'POST' :
        form = FeedbackForm(request.POST, request.FILES)
        if form.is_valid():
            form.save()
            context = getCommonContext(request)
            context.update({'message' : 'Cám ơn bạn đã góp ý cho chúng tôi.'})
            return render_to_response('ncs/simple_message.html', context)
    else:
        form = FeedbackForm()
    return render_to_response("papershare/feedback_form.html",
                              { 'form': form }
                              )
def static(request, template = None):
    if template is not None:
        return render_to_response(template)