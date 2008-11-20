from ncs.papershare.models import Request
from django.contrib.auth.models import User
from django.conf import settings
from django.template.loader import render_to_string
from django.contrib.sites.models import Site
from django.db.models import Q

from ncs.papershare.models import PaperShareProfile
from ncs.papershare.models import REQUEST_STATUS_CHOICES
from ncs.utils.sendmail import sendmailFromTemplate

from datetime import datetime
import re

FAIL_LIMIT = 3

def findSupplier(request):
    if (request.previously_assigned  is not None and len(request.previously_assigned.split(";")) >= FAIL_LIMIT + 1):
        print "Request %s (%s) is failed , last_assignment : %s" % (unicode(request), 
                                                          request.date_requested, 
                                                          request.previously_assigned)
        request.status = REQUEST_STATUS_CHOICES[5][0] #"failed"
        request.save();
        #TODO : send email to requester
        return
    
    try:    
        supplierProfile = PaperShareProfile.objects.filter(is_supplier=True,research_field=request.paper.research_field).filter(~Q(user=request.requester)).order_by('last_assignment')[0]
    except IndexError:
        print "!!!!! Field %s doesn't have any supplier [%s]" % (request.paper.research_field, request)
        return
    
    request.supplier = supplierProfile.user
    last_assignment = supplierProfile.last_assignment
    
    if last_assignment is not None:
        t = max(datetime.now() , last_assignment) 
    else:
        t = datetime.now()
    supplierProfile.last_assignment = datetime(t.year, t.month, t.day, t.hour, t.minute + 1, t.second) #make sure the user will not get request until next minute  
    supplierProfile.save()
     
    request.date_assigned = t
    if request.previously_assigned is None:
        request.status = REQUEST_STATUS_CHOICES[1][0] #"assigned"
        request.previously_assigned = ";" + supplierProfile.user.username
    else:
        request.status = REQUEST_STATUS_CHOICES[2][0] #"re-assigned"
        #TODO : make sure username does not contain ";"
        request.previously_assigned += ";" + supplierProfile.user.username
    request.save()    
    sendReminderEmailToSupplier(request)
    print "Request %s (%s) is assigned to user %s(%s)" % (unicode(request), 
                                                          request.date_requested, 
                                                          unicode(supplierProfile.user),
                                                          last_assignment)

def sendReminderEmailToSupplier(request):
    current_site = Site.objects.get_current()
    template_name = 'papershare/supplier_email.txt'
    context = { 'request': request ,
                'site' : current_site}
    subject = "%s wanted" % request.paper.title
    
    sendmailFromTemplate(template_name = template_name,
                         toAddr = request.supplier.email, 
                         subject = subject, 
                         context = context)

def main(argv):
    print "Running request scheduler"
    requestQueue = Request.objects.filter(status__exact=0).order_by('date_requested')
    #requestQueue = Request.objects.all().order_by('date_requested')
    for request in requestQueue:
        if request.status == 0:
            findSupplier(request)
    del requestQueue
    
