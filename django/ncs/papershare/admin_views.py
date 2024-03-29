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
    Chuyá»ƒn cÃ¡c request Ä‘Ã£ giao cho ngÆ°á»�i nÃ y sang tráº¡ng thÃ¡i pending Ä‘á»ƒ assign cho supplier khÃ¡c, 
    ngoáº¡i trá»« nhá»¯ng request nÃ o cÃ³ status supplied, re-assigned vÃ  thanked
    """
    Request.objects.filter(
        Q(supplier=supplier_id),
        Q(status__in=[REQ_STA_ASSIGNED, REQ_STA_REASSIGNED, REQ_STA_LASTCHANCE])
    ).update(status=0)

 
def reinitialize(object_list):
    """
    Xá»­ lÃ½ dá»¯ liá»‡u
    """
    i = 0
    result = []
    days_late = 0
    for item in object_list:
        SUPPLIED_STATUS = [REQ_STA_SUPPLIED, REQ_STA_THANKED]
        BAD_STATUS = [REQ_STA_PENDING, REQ_STA_ASSIGNED, REQ_STA_REASSIGNED, REQ_STA_LASTCHANCE]
        m =re.compile(';'+item.user.username)
        findstring = lambda x: m.search(x)
        a1, a2 = 0,0
        a3 = (Request.objects.filter(supplier=item.user.id).count())
        a4 = str(Request.objects.filter(Q(previously_supplied__contains=item.user.username)).count())        
        a5, a6 = 0,0
        a7 = str(Request.objects.filter( Q(supplier=item.user.id),\
                                         Q(status__in=[REQ_STA_PENDING, REQ_STA_ASSIGNED,\
                                                        REQ_STA_REASSIGNED, REQ_STA_LASTCHANCE])).count())
        a8 = (Request.objects.filter( Q(supplier=item.user.id),\
                                         Q(status__in=[REQ_STA_REASSIGNED])).count())
        days_late = 0
        for raw in Request.objects.filter(Q(supplier=item.user.id)):
            if raw.previously_supplied != '':
                a5 = a5 + 1 # Sá»‘ bÃ i bÃ¡o Ä‘Æ°á»£c cung cáº¥p bá»Ÿi supplier khÃ¡c
            if raw.status in SUPPLIED_STATUS: 
                a6 = a6 +1 # Sá»‘ bÃ i bÃ¡o Ä‘Ã£ cung cáº¥p
            if raw.date_supplied is None:
                try:
                    if (datetime.datetime.now()-raw.date_assigned).days > 2:
                        a2 = a2+1
                        if (datetime.datetime.now()-raw.date_assigned).days>days_late:
                            days_late = (datetime.datetime.now()-raw.date_assigned).days
                except TypeError:
                    days_late = 0
            if raw.date_passed not in ('', '0000-00-00 00:00:00'):
                try:
                    if (raw.date_passed-raw.date_assigned).days > 2:
                        a1 = a1 + 1 # Sá»‘ bÃ i bÃ¡o chuyá»ƒn trá»…
                except TypeError:
                    pass
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
                       'paper_supplied':int(a6)-int(a5),#so bai bao da cung cap
                       'paper_waiting':int(a7),# so bai bao dang cho
                       'paper_passed':a8, #so bai bao da chuyen
                       'paper_help_supplied':a4, # so bai bao da cung cap cho supplier khac
                       'username':item.user.username,
                       'userid':item.user.id,
                       'last_login':item.user.last_login,
                       'date_joined':item.user.date_joined,
                       'research_field':research_name[0][1],
                       })
    return result

def supplier_change_list(request):
    """
    Hiá»ƒn thá»‹ danh sÃ¡ch cÃ¡c supplier cÃ¹ng cÃ¡c thÃ´ng tin kÃ¨m theo.
    """
    template_name= 'admin/papershare/supplier_change_list.html'
    item_per_page = 100
    current_page = 1

    if request.POST.get('action', '') == 'delete_selected':
        """
        Xá»­ lÃ½ cÃ¡c supplier bá»‹ disable
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
    #import logging
    #logging.basicConfig(level=logging.DEBUG, filename="debug.log")
    #logging.info(len(supplier_list))
    if len(supplier_list)==0:
        return redirect('/papershare/admin/')
    paging = Paginator(supplier_list, item_per_page)
    try:
        p = paging.page(current_page)
    except InvalidPage, EmptyPage:
        research_field_exact = request.GET.get('research_field__exact', '')        
        return redirect('/papershare/admin/papershare/supplier/?research_field__exact='+research_field_exact)
    
    """ 
    Danh sÃ¡ch cÃ¡c phÃ¢n trang
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
        
        sendmailFromHtml(settings.DEFAULT_FROM_EMAIL ,supplier_disable.user.email, _('Báº¡n nháº­n Ä‘Æ°á»£c 1 email tá»« nghiencuusinh.org'),content)
        
    vars_assign = {'mail_content':mail_content,
                   'content':content}
    return render_to_response(template_name, vars_assign, RequestContext(request))
