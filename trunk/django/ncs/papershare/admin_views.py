#-*-coding:UTF-8-*-
import re, datetime, operator

from django.template import Context, Template
from django.shortcuts import render_to_response, redirect
from django.contrib.auth.models import User
from django.contrib.admin.views.decorators import staff_member_required
from django.template import RequestContext
from django.core.paginator import Paginator, EmptyPage, InvalidPage
from django.conf import settings
from django.db.models import Q
from django.utils.datastructures import MultiValueDictKeyError
from django.utils.translation import ugettext_lazy as _
from ncs.utils.sendmail import sendmailFromHtml

from ncs.papershare.models import Announcement, Request, PaperShareProfile, \
REQ_STA_PENDING ,REQ_STA_ASSIGNED, REQ_STA_REASSIGNED, \
REQ_STA_SUPPLIED, REQ_STA_THANKED, REQ_STA_FAILED, REQ_STA_LASTCHANCE

from ncs.papershare.models import PaperShareProfile, RESEARCH_FIELDS, Supplier

def disable_supplier(supplier_id):
    """ Disable supplier """
    PaperShareProfile.objects.filter(Q(user__in=supplier_id)).update(is_supplier=0)
    """
    Chuyển các request đã giao cho người này sang trạng thái pending để assign cho supplier khác, 
    ngoại trừ những request nào có status supplied, re-assigned và thanked
    """
    Request.objects.filter(
        Q(supplier=supplier_id),
        Q(status__in=[REQ_STA_ASSIGNED, REQ_STA_REASSIGNED, REQ_STA_LASTCHANCE])
    ).update(status=0)

 
def reinitialize(object_list):
    """
    Xây dựng lại cấu trúc dữ liệu
    """
    i = 0
    result = []
    for item in object_list:
        if item.user.username != '':
            a3 = (Request.objects.filter(supplier=item.user.id).count())
            SUPPLIED_STATUS = [REQ_STA_SUPPLIED, REQ_STA_THANKED]
            BAD_STATUS = [REQ_STA_PENDING, REQ_STA_ASSIGNED, REQ_STA_REASSIGNED, REQ_STA_LASTCHANCE]
            m =re.compile(';%s' % item.user.username)
            findstring = lambda x: m.search(x)
            #a4 = str(Request.objects.filter(Q(supplier=item.user.id),Q(status__in=BAD_STATUS)).count())        
            a5,a6 = 0,0
            for raw in Request.objects.filter(Q(previously_assigned__contains=item.user.username)):
                try:
                    if raw.previously_assigned.split(';')[1] != item.user.username:
                        a5 = a5 + 1
                except:
                    pass
                if raw.status in SUPPLIED_STATUS: 
                    if findstring(raw.previously_assigned): 
                        a6 = a6 +1
            a7 = str(Request.objects.filter( Q(supplier=item.user.id),\
                                             Q(status__in=[REQ_STA_PENDING, REQ_STA_ASSIGNED,\
                                                            REQ_STA_REASSIGNED, REQ_STA_LASTCHANCE])).count())
            a1, a2, a4, a8 = 0,0,0,0
            for raw in Request.objects.filter(Q(supplier=item.user.id)):
                days_late = 0
                if raw.date_supplied:
                    if (raw.date_supplied-raw.date_assigned).days > 2:
                        a4 = a4+1
                else:
                    if (datetime.datetime.now()-raw.date_assigned).days > 2:
                        a2 = a2+1
                        if int(a7)>0:
                            if (datetime.datetime.now()-raw.date_assigned).days>days_late:
                                days_late = (datetime.datetime.now()-raw.date_assigned).days
                if raw.date_passed:
                    if (raw.date_passed-raw.date_assigned).days > 2:
                        a1 = a1+1                
                    a8 = a8 + 1
            i = i + 1
            research_name = filter(lambda x: x[0]==item.research_field, RESEARCH_FIELDS)
            item.user.last_login = (datetime.datetime.today() - item.user.last_login).days
            result.append({
                           'paper_late_passed':a1,# so bai bao chuyen tre
                           'paper_late_now':a2,#so bai bao hien dang tre
                           'days_late':days_late,# so ngay tre
                           'paper_supply':a3,#so bai bao duoc phan cong
                           'late_supply':a4,#so bai bao cung cap tre
                           'paper_someone_supplied':a5,#so bai bao duoc cung cap boi supplier khac
                           'paper_supplied':a6,#so bai bao da cung cap
                           'paper_waiting':a7,# so bai bao dang cho
                           'paper_passed':a8, #so bai bao da chuyen
                           'paper_help_supplied':max(0,int(a3)-int(a5)),
                           'username':item.user.username,
                           'userid':item.user.id,
                           'last_login':item.user.last_login,
                           'date_joined':item.user.date_joined,
                           'research_field':research_name[0][1],
                           })
    return result

def supplier_change_list(request):
    """
    Hiển thị danh sách các supplier cùng các thông tin kèm theo.
    """
    template_name= 'admin/papershare/supplier_change_list.html'
    item_per_page = 100
    current_page = 1

    if request.POST.get('action', '') == 'delete_selected':
        """
        Xử lý các supplier bị disable
        """
        template_name= 'admin/papershare/supplier_delete_confirmation.html'
        if request.POST.get('post', '') == 'yes':
            try:
                disable_supplier(request.POST.getlist('_selected_action'))
            except:
                return redirect('/papershare/admin/papershare/supplier/')
        vars_assign = {
            'supplier':PaperShareProfile.objects.filter(user__in=request.POST.getlist('_selected_action')) 
        }
        return render_to_response(template_name, vars_assign, RequestContext(request))

    current_page = int(request.GET.get('p', 1))
    research_field_exact = request.GET.get('research_field__exact', '')
    
    supplier_list = PaperShareProfile.objects.filter(
                    Q(is_supplier=1),
                    Q(research_field__contains=research_field_exact))
    
    paging = Paginator(supplier_list, item_per_page)
    try:
        p = paging.page(current_page)
    except InvalidPage, EmptyPage:
        research_field_exact = request.GET.get('research_field__exact', '')        
        return redirect('/papershare/admin/papershare/supplier/?research_field__exact='+research_field_exact)
    
    """ 
    Danh sách các phân trang
    """
    pages = [n for n in range(current_page-5,current_page+5+1) if n>=1 and n<=paging.num_pages]
    
    supplier_list = reinitialize(p.object_list)
    sort_type = request.GET.get('sort', 'asc')
    b_reverse = False
    if sort_type == 'desc':
        sort_type = 'asc'
        b_reverse = True
    elif sort_type == 'asc':
        sort_type = 'desc'
    field = request.GET.get('field', '')
    if field != '':
        supplier_list = sorted(supplier_list, key=operator.itemgetter(field),reverse = b_reverse)
    request_query = '?'
    for key,value in request.GET.iteritems():
        request_query = request_query + '%s=%s&'%(key,value)
    vars_assign = {'supplier_list': supplier_list,
                    'filters': RESEARCH_FIELDS,
                    'pages': pages,
                    'current_page':current_page,
                    'request_query':request_query,
                    'request':request.GET,
                    'sort_type':sort_type,
                    'paging': paging}
    
    return render_to_response(template_name, vars_assign, RequestContext(request))

def supplier_change_form(request, supplier_id):
    template_name= 'admin/papershare/supplier_change_form.html'
    mail_content = Announcement.objects.filter(type='EM')
    for item in mail_content:
        pass 
    default_mail_content = content = ''
    disable = False
    supplier_disable = PaperShareProfile.objects.get(user=supplier_id)
    a2 = 0
    
    if supplier_disable.is_supplier == 0:
        return redirect('/papershare/admin/papershare/supplier')
    
    if request.method == 'POST':
        announcement_id = int(request.POST['announcement_id'])
        default_mail_content = Announcement.objects.get(pk=announcement_id).content
        t = Template(default_mail_content)
        a2 = 0
        try:
            disable = request.POST['disable']
        except:
            disable = False
        if disable == True:
            disable_supplier([supplier_id])
        else:
            for raw in Request.objects.filter(Q(supplier=supplier_id)):
                if raw.date_supplied=='':
                    if (datetime.datetime.now()-raw.date_assigned).days > 2:
                        a2 = a2+1
            
        c = Context({"num_exp_paper": a2})
        try: 
            content = t.render(c) + request.POST['content'] + '<br/>BQT.'
        except:
            content = t.render(c) + '<br/>BQT.'
        
        sendmailFromHtml(settings.DEFAULT_FROM_EMAIL ,supplier_disable.user.email, _('Bạn nhận được 1 email từ nghiencuusinh.org'),content)
        
    vars_assign = {'mail_content':mail_content,
                   'content':content}
    return render_to_response(template_name, vars_assign, RequestContext(request))