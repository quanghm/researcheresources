#!/usr/bin/env python
# -*- coding: UTF-8 -*-

from django.shortcuts import render_to_response
from django.http import HttpResponseRedirect, HttpResponse
from django.contrib.auth.models import User
from django.template import RequestContext
from django.views.generic import list_detail
from django.contrib.auth.decorators import login_required

from models import Announcement, Request, PaperShareProfile
from forms import PaperRequestForm, PaperUploadForm, FeedbackForm, ContactUserForm
from ncs.settings import SHARE_DIR_ROOT, SHARE_DIR_URL
from ncs.utils.sendmail import sendmailFromTemplate
from ncs.communication.emails import sendReminderEmailToRequester
from ncs.papershare.models import REQUEST_STATUS_CHOICES, REQ_STA_PENDING ,REQ_STA_ASSIGNED, REQ_STA_REASSIGNED, REQ_STA_SUPPLIED, REQ_STA_THANKED, REQ_STA_FAILED, REQ_STA_LASTCHANCE

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
    try:
        user_supplier = PaperShareProfile.objects.get(user=user).is_supplier
    except PaperShareProfile.DoesNotExist:
        user_supplier = None
    return {"requested" : Request.objects.filter(requester=user, status__in = PUBLIC_POOL_ACCEPTED_STATUSES).count(),
            "to_serve" : Request.objects.filter(supplier=user, status__in = PUBLIC_POOL_ACCEPTED_STATUSES).count(),
            "is_supplier" : user_supplier
    }
def mypage(request):
    #check if user logged in
    if not request.user.is_authenticated():
        return HttpResponseRedirect("/papershare/")
    
    announcements = Announcement.objects.order_by('-date')
    context = RequestContext(request)
    context.update({
            "announcements" : announcements
            }).update(get_my_stats(request.user.id))

    """
    Danh sách:
    - Top 10 supplier, dựa vào số lượng bài cung cấp.
    - Top 10 requester, dựa vào số lần gửi yêu cầu.
    
    from django.db import connection
    cursor = connection.cursor()
    top_number = 10
    cursor.execute("select u.username, u.id, count(r.supplier_id) as `total` from papershare_request r inner join auth_user u on u.id = r.supplier_id where r.status in (%d, %d) group by r.supplier_id order by total DESC limit 0,%d " % (REQ_STA_SUPPLIED, REQ_STA_THANKED, top_number))
    top_supplier = cursor.fetchall()
    cursor.execute("SELECT u.username, u.id, count(p.requester_id) as `total` FROM papershare_request p left join auth_user u on u.id=p.requester_id group by p.requester_id order by `total` DESC LIMIT 0,%d " % (top_number))
    top_requester = cursor.fetchall()
    
    """

    from django.db.models import Count
    suppliers = Request.objects.filter(status__in=[REQ_STA_SUPPLIED, REQ_STA_THANKED]).values('supplier').annotate(total=Count('supplier')).order_by('-total')[0:10]
    top_supplier=[]
    for i in suppliers:
        u=User.objects.get(pk=i['supplier'])
        top_supplier.append({'user_name':u.username,'user_id':u.pk, 'total':i['total']})
    
    requesters = Request.objects.values('requester').annotate(total=Count('requester')).order_by('-total')[0:10]
    top_requester=[]
    for i in requesters:
        u=User.objects.get(pk=i['requester'])
        top_requester.append({'user_name':u.username,'user_id':u.pk, 'total':i['total']})
    
    context.update({
        "top_supplier": top_supplier,
        "top_requester": top_requester,
    })
    return render_to_response('ncs/mypage.html', context)

@login_required
def requestPaper(request):
    if request.method == 'POST':
        form = PaperRequestForm(data=request.POST, files=request.FILES)
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
        form = PaperRequestForm()
    
    context = RequestContext(request)
    context.update(get_my_stats(request.user))
    return render_to_response("papershare/paper_request.html",
                              { 'form': form },
                              context_instance=context)
    
@login_required
def listRequests(request,page=1):
    if not request.user.is_authenticated():
        return HttpResponseRedirect("/papershare/")
    queryset = Request.objects.filter(requester__exact=request.user, status__in = PUBLIC_POOL_ACCEPTED_STATUSES)
    extra_context = get_my_stats(request.user)
    
    return list_detail.object_list(request, queryset=queryset, 
                                   template_object_name = "request" , 
                                   extra_context = extra_context,
                                   paginate_by = 10,
                                   page = page)
@login_required
def listRequestsToSupply(request,page=1):
    queryset = Request.objects.filter(supplier__exact=request.user, status__in = PUBLIC_POOL_ACCEPTED_STATUSES)
    extra_context = get_my_stats(request.user)
    
    return list_detail.object_list(request, queryset=queryset, 
                                   template_object_name = "request" , 
                                   extra_context = extra_context,
                                   paginate_by = 10,
                                   page = page)

PUBLIC_POOL_ACCEPTED_STATUSES = [REQ_STA_PENDING ,REQ_STA_ASSIGNED, REQ_STA_REASSIGNED, REQ_STA_LASTCHANCE]

@login_required
def showPublicPool(request,field=None, page=1):
    try:
        #my_research_field = User.objects.get(pk = request.user.id).get_profile().research_field
        #queryset = Request.objects.filter(status__in = PUBLIC_POOL_ACCEPTED_STATUSES, paper__research_field__exact = my_research_field)
        if field is None:
            queryset = Request.objects.filter(status__in = PUBLIC_POOL_ACCEPTED_STATUSES).order_by('date_requested')
        else:
            queryset = Request.objects.filter(status__in = PUBLIC_POOL_ACCEPTED_STATUSES, paper__research_field__exact = field).order_by('date_requested')
    except User.DoesNotExist:
        queryset = Request.objects.filter(status__in = PUBLIC_POOL_ACCEPTED_STATUSES)
    
    extra_context = get_my_stats(request.user)
    
    return list_detail.object_list(request, queryset=queryset, 
                                   template_object_name = "request" , 
                                   extra_context = extra_context,
                                   paginate_by = 10,
                                   page = page)

TRASH_POOL_ACCEPTED_STATUSES = [REQ_STA_FAILED]
@login_required
def showTrashPool(request,page=1):
    try:
        my_research_field = User.objects.get(pk = request.user.id).get_profile().research_field
        queryset = Request.objects.filter(status__in = TRASH_POOL_ACCEPTED_STATUSES, paper__research_field__exact = my_research_field)
    except User.DoesNotExist:
        queryset = Request.objects.filter(status__in = TRASH_POOL_ACCEPTED_STATUSES)
    
    extra_context = get_my_stats(request.user)
    
    return list_detail.object_list(request, queryset=queryset, 
                                   template_object_name = "request" , 
                                   extra_context = extra_context,
                                   paginate_by = 10,
                                   page = page)


@login_required    
def detailRequest(request, object_id):
    queryset = Request.objects.all()
    extra_context = get_my_stats(request.user)
    extra_context.update({'form': PaperUploadForm()})
    return list_detail.object_detail(request, object_id=object_id, 
                                     queryset=queryset, 
                                     template_name = "papershare/request_detail.html", 
                                     template_object_name = "request" , 
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
                
                if paperRequest.supplier is None :
                    paperRequest.supplier = request.user
                    context.update({'realSupplier' : request.user})
                elif paperRequest.supplier.id != request.user.id:
                    print "Process for other supplier ", paperRequest.supplier
                    context.update({'realSupplier' : request.user})
                    sendmailFromTemplate(toAddr=paperRequest.supplier.email,
                                     subject=u"Some one has provided a paper request that was assigned to you",
                                     template_name="papershare/request_processed_email.html",
                                     context=context)
                    paperRequest.supplier = request.user
                
                
                
                sendmailFromTemplate(fromAddr=request.user.email, toAddr=paperRequest.requester.email,
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
                paperRequest.status = REQ_STA_FAILED #failed.
                paperRequest.save()
                sendReminderEmailToRequester(paperRequest)
                message = u"Yêu cầu %d da duoc chuyen vao trash pool" % paperRequest.id
                context.update({'message' : message}) 
                return render_to_response('ncs/simple_message.html', context)
        elif request.POST.get("buttonAssign") is not None:
            """
            Khi admin chuyen bai bao cho mot thanh vien khac thi:
            - Neu user dc chuyen toi chua la supplier thi set thanh supplier va thong bao cho user biet.
            - Thong bao cho supplier cu cua bai bao do duoc biet.
            """
            if request.POST['username'] != "":

                user_name   = request.POST['username']
                request_id = request.POST['request_id']
                user_obj = User.objects.get(username=user_name)
                if user_obj is not None:
                    request_obj = Request.objects.get(pk=request_id)
                    
                    from django.core.mail import send_mail
                    from django.conf import settings
                    full_path="http://"+request.get_host()+request.get_full_path()
                    send_mail("Ban nhan duoc email tu nghiencuusinh.org",
                                "Co mot bai bao vua duoc chuyen sang cho ban"
                                +"Click vao day de xem chi tiet "+ full_path,
                                settings.DEFAULT_FROM_EMAIL,
                                [user_obj.email])
                    send_mail("Ban nhan duoc email tu nghiencuusinh.org",
                                "Bai bao "+request_obj.paper.title+" vua duoc chuyen sang cho thanh vien "+user_obj.email
                                +"Click vao day de xem chi tiet"+ full_path,
                                settings.DEFAULT_FROM_EMAIL,
                                [request_obj.supplier.email])
                    
                    u_profile=PaperShareProfile.objects.get(user=user_obj)
                    if u_profile.is_supplier is not True:
                        u_profile.is_supplier=True
                        u_profile.save()
                    return render_to_response("ncs/simple_message.html", {"message":"Bài báo đã được chuyển cho các thành viên liên quan."})
                else:
                    return render_to_response("ncs/simple_message.html", {"message":"Không tìm thấy thành viên "+user_name})
    return HttpResponseRedirect("/papershare/")

def handle_uploaded_file(f):
    #see tempfile note
    #http://utcc.utoronto.ca/~cks/space/blog/python/UsingTempfile
    stdizedFileName = re.sub("[\s]","_",f.name)
    fd , fileName = mkstemp(stdizedFileName,"uploaded/",SHARE_DIR_ROOT)
    
    destination = os.fdopen(fd, "w+b")
    for chunk in f.chunks():
        destination.write(chunk)
    destination.close()
    relFileName = fileName[len(SHARE_DIR_ROOT)+1:]
    relFileName = re.sub("\\\\", "/", relFileName)
    return SHARE_DIR_URL + relFileName

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
@login_required
def contact(request, toUserId):
    if request.method == 'POST' :
        form = ContactUserForm(request.POST, request.FILES)
        if form.is_valid():
            form.save()
            context = getCommonContext(request)
            context.update({'message' : 'Your message has been sent'})
            return render_to_response('ncs/simple_message.html', context)
    else:        
        form = ContactUserForm()
        form.setInitial(request.user, User.objects.get(id=toUserId))
    return render_to_response("papershare/contact_form.html",
                              { 'form': form }
                              )

@login_required
def contactPaper(request, requestId):
    if request.method == 'POST' :
        form = ContactUserForm(request.POST, request.FILES)
        if form.is_valid():
            form.save()
            context = getCommonContext(request)
            context.update({'message' : 'Your message has been sent'})
            return render_to_response('ncs/simple_message.html', context)
    else:        
        paperRequest = Request.objects.get(id=requestId)
       
        subject = u"Bài báo của bạn :" + paperRequest.paper.title 
        content = u"Chào bạn " + paperRequest.requester.username + u",\n" \
                + u"Đây là bài báo mà tôi tìm được giúp bạn \n" \
                + u"Thân, \n" \
                + request.user.username 
                
        form = ContactUserForm()
        form.setInitial(request.user, 
                        paperRequest.requester,
                        subject, content)
    return render_to_response("papershare/contact_form.html",
                              { 'form': form }
                              )
    
    
def static(request, template = None):
    if template is not None:
        return render_to_response(template)

@login_required
def lazysupplier(request, sid):
    """
    Le Dinh Thuong
    navaroiss@gmail.com
    """
    if request.user.is_staff:
        from ncs.papershare.forms import LazySupplierForm
        from django.db.models import Q
        supplier = User.objects.get(id=int(sid))
        admin = request.user

        context = getCommonContext(request)
        context.update({"supplier":supplier})
        context.update({"number_supplied":Request.objects.filter(
            Q(supplier=supplier.id),
            Q(status__in = [REQ_STA_SUPPLIED, REQ_STA_THANKED])).count()
        })
        context.update({"number_wait_supply":Request.objects.filter(
            Q(supplier=supplier.id),
            Q(status__in=[REQ_STA_PENDING, REQ_STA_ASSIGNED, REQ_STA_REASSIGNED, REQ_STA_LASTCHANCE])).count()
        })

        if request.method == "POST":
            form = LazySupplierForm(request.POST)
            context.update({"form":form})
            if form.is_valid():
                form.alertSupplier(supplier)
                return render_to_response("papershare/lazy_supplier_complete.html", context)
            else:
                return render_to_response("papershare/lazy_supplier.html", context)
        else:
            form = LazySupplierForm()
            form.setInitial(supplier, admin)
        return render_to_response("papershare/lazy_supplier.html", context)
    else:
        return render_to_response("404.html")
