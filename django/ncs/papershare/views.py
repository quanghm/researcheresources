# -*- coding: UTF-8 -*-

import datetime,os,re
from tempfile import mkstemp

from django.shortcuts import render_to_response
from django.http import HttpResponseRedirect, HttpResponse
from django.contrib.auth.models import User
from django.template import RequestContext
from django.views.generic import list_detail
from django.contrib.auth.decorators import login_required
from django.core.mail import send_mail
from django.conf import settings
from django.db.models import Count
from django.db.models import Q
from django.utils.translation import ugettext, ugettext_lazy as _

from ncs.papershare.models import Announcement, Request, PaperShareProfile, \
REQ_STA_PENDING ,REQ_STA_ASSIGNED, REQ_STA_REASSIGNED, \
REQ_STA_SUPPLIED, REQ_STA_THANKED, REQ_STA_FAILED, REQ_STA_LASTCHANCE
from ncs.papershare.forms import LazySupplierForm, PaperRequestForm, \
PaperUploadForm, FeedbackForm, ContactUserForm

from ncs.settings import SHARE_DIR_ROOT, SHARE_DIR_URL
from ncs.utils.sendmail import sendmailFromTemplate
from ncs.communication.emails import sendReminderEmailToRequester 


def homepage(request):
    #if user is logged in, redirect to mypage
    if request.user.is_authenticated():
        return HttpResponseRedirect("/papershare/mine/")
    
    announcements = Announcement.objects.filter(type='AN').order_by('-date')
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
    
    announcements = Announcement.objects.filter(type='AN').order_by('-date')
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
                    """
                    Neu nhu bai bao chua duoc assign cho 1 supplier thi set supplier do chinh la user hien tai.
                    """
                    paperRequest.supplier = request.user
                    context.update({'realSupplier' : request.user})
                    
                elif paperRequest.supplier.id != request.user.id:
                    """
                    Process for other supplier ", paperRequest.supplier
                    Neu nguoi cung cap bai bao khong phai la nguoi duoc assign thi tinh diem cho nguoi da cung cap.
                    """
                    context.update({'realSupplier' : request.user})
                    sendmailFromTemplate(toAddr=paperRequest.supplier.email,
                                     subject=_(u"Some one has provided a paper request that was assigned to you"),
                                     template_name="papershare/request_processed_email.html",
                                     context=context)
                    if paperRequest.previously_supplied is None:
                        paperRequest.previously_supplied = ""
                    paperRequest.previously_supplied += ';' + request.user.username

                paperRequest.date_supplied = datetime.datetime.now()
                paperRequest.save()
                
                sendmailFromTemplate(fromAddr=request.user.email, toAddr=paperRequest.requester.email,
                                     subject=_(u"Good news ! your paper request has been processed"),
                                     template_name="papershare/request_processed_email.html",
                                     context=context)            
                return render_to_response('papershare/upload_complete.html',context)
            else:
                context.update({'form':form})
                return render_to_response('papershare/upload_error.html',context)
        
        elif request.POST.get("buttonFail") is not None: #report fail by admin
            requestId = request.POST.get('request_id')
            if requestId is not None and requestId.isdigit():
                paperRequest = Request.objects.get(id=int(requestId))
                paperRequest.supplier = None
                paperRequest.status = REQ_STA_FAILED #failed.
                paperRequest.save()
                sendReminderEmailToRequester(paperRequest)
                message = _(u"Yêu cầu %d da duoc chuyen vao trash pool" % paperRequest.id)
                context.update({'message' : message}) 
                return render_to_response('ncs/simple_message.html', context)
            
        elif request.POST.get("buttonAssign") is not None: # Chuyển bài báo cho 1 supplier khác
            """
            Khi admin chuyen bai bao cho mot thanh vien khac thi:
            - Neu user dc chuyen toi chua la supplier thi set thanh supplier va thong bao cho user biet.
            - Thong bao cho supplier cu cua bai bao do duoc biet.
            """
            
            request_id   = request.POST.get("request_id")
            paperRequest = Request.objects.get(id=int(request_id))
            requester    = paperRequest.requester
            oldSupplier  = paperRequest.supplier
            newSupplier  = User()

            suggestedNewSupplierUsername = request.POST.get("username")

            try:
                if suggestedNewSupplierUsername == "":
                    newSupplier = random_new_supplier(requester, oldSupplier)
                else:
                    newSupplier = confirmed_suggested_new_supplier(suggestedNewSupplierUsername, requester, oldSupplier)

            except NoSuppliersInResearchField , field:
                return render_to_response("ncs/simple_message.html", {"message":_(u"Không có người cung cấp trong ngành ") + field.parameter})

            except User.DoesNotExist:
                return render_to_response("ncs/simple_message.html", {"message":_(u"Không tìm thấy thành viên ") + suggestedNewSupplierUsername})

            except UserIsNotSupplier:
                return render_to_response("ncs/simple_message.html", {"message":_(u"Không tìm thấy người cung cấp  ") + suggestedNewSupplierUsername})


            paperRequest.status = REQ_STA_REASSIGNED; #reassigned, pending. TODO check this
            paperRequest.data_passed = datetime.datetime.now()
            if oldSupplier is not None: 
                paperRequest.previously_assigned += ';' + oldSupplier.username
            paperRequest.supplier_id = newSupplier.id

            full_path = "http://"+request.get_host()+request.get_full_path()
            send_mail(_(u"Ban nhan duoc email tu nghiencuusinh.org"),
                        _(u"Co mot bai bao vua duoc chuyen sang cho ban. Click vao day de xem chi tiet ")
                        +full_path,
                        settings.DEFAULT_FROM_EMAIL,
                        [newSupplier.email])
            if oldSupplier is not None:
                send_mail(_(u"Ban nhan duoc email tu nghiencuusinh.org"),
                        _(u"Bai bao "+paperRequest.paper.title+" vua duoc chuyen sang cho thanh vien "+newSupplier.email+". Click vao day de xem chi tiet ")+ full_path,
                        settings.DEFAULT_FROM_EMAIL,
                        [oldSupplier.email])

            paperRequest.save()

            return render_to_response("ncs/simple_message.html", {"message":_(u"Bài báo đã được chuyển cho các thành viên " + newSupplier.username + ".")})

    return HttpResponseRedirect("/papershare/")


class NoSuppliersInResearchField(Exception):
    def __init__(self, value):
       self.parameter = value
    def __str__(self):
       return repr(self.parameter)

class UserIsNotSupplier(Exception):
   def __init__(self, value):
       self.parameter = value
   def __str__(self):
       return repr(self.parameter)

class UserDoesNotExist(Exception):
   def __init__(self, value):
       self.parameter = value
   def __str__(self):
       return repr(self.parameter)

def confirmed_suggested_new_supplier(suggestedNewSupplierUsername, requester, oldSupplier):
    suggestedNewSupplier        = User.objects.get(username=suggestedNewSupplierUsername)
    suggestedNewSupplierProfile = PaperShareProfile.objects.get(user=suggestedNewSupplier.id)

    if not suggestedNewSupplierProfile.is_supplier:
        raise UserIsNotSupplier(suggestedNewSupplier.username)

    else:
        return suggestedNewSupplier         

# select a new random supplier from the database
# who is in the same field with the *requester*
#   alternatively, one-could select the new supplier
#   from the same field as the old supplier
def random_new_supplier(requester, oldSupplier):    

    from django.db import connection

    try:
        notToSameSupplierPhrase = ""
        if oldSupplier is not None and oldSupplier.id is not None:
            notToSameSupplierPhrase = "and user_id!='%s'" % (oldSupplier.id)
        requesterProfile=PaperShareProfile.objects.get(user=requester.id)
        command = "select user_id from %s.papershare_papershareprofile where is_supplier='1' and research_field='%s' and user_id!='%s' %s order by rand() limit 1" % (settings.DATABASE_NAME, requesterProfile.research_field, requester.id, notToSameSupplierPhrase)
        cursor=connection.cursor()
        cursor.execute(command)
        randomNewSupplierId = cursor.fetchone()[0]
        return User.objects.get(id=randomNewSupplierId)

    except:
        raise NoSuppliersInResearchField(requesterProfile.research_field)


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
            context.update({'message' : _(u'Cám ơn bạn đã góp ý cho chúng tôi.')})
            return render_to_response('ncs/simple_message.html', context)
    else:
        form = FeedbackForm()
    return render_to_response("papershare/feedback_form.html",
                              { 'form': form })
@login_required
def contact(request, toUserId):
    if request.method == 'POST' :
        form = ContactUserForm(request.POST, request.FILES)
        if form.is_valid():
            form.save()
            context = getCommonContext(request)
            context.update({'message' : _(u'Your message has been sent')})
            return render_to_response('ncs/simple_message.html', context)
    else:        
        form = ContactUserForm()
        form.setInitial(request.user, User.objects.get(id=toUserId))
    return render_to_response("papershare/contact_form.html",
                              { 'form': form })

@login_required
def contactPaper(request, requestId):
    if request.method == 'POST' :
        form = ContactUserForm(request.POST, request.FILES)
        if form.is_valid():
            form.save()
            context = getCommonContext(request)
            context.update({'message' : _(u'Your message has been sent')})
            return render_to_response('ncs/simple_message.html', context)
    else:        
        paperRequest = Request.objects.get(id=requestId)
       
        subject = _(u"Bài báo của bạn :" + paperRequest.paper.title) 
    #    subject = settings.EMAIL_HOST
        content = _(u"Chào bạn " + u"" + u",\n" \
                + u"Đây là bài báo mà tôi tìm được giúp bạn \n" \
                + u"Thân, \n" \
                + request.user.username) 
        form = ContactUserForm()
        form.setInitial(request.user, 
                        paperRequest.requester,
                        subject, content)
    return render_to_response("papershare/contact_form.html",
                              { 'form': form })

def static(request, template = None):
    if template is not None:
        return render_to_response(template)

@login_required
def lazysupplier(request, sid):
    """
    Xử lý supplier lười biếng theo 2 phương thức:
    - Nhắc nhỏ supplier.
    - Disable.
    Chỉ cho phép staff vào trang này.
    """
    if request.user.is_staff:
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
