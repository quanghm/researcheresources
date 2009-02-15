from django.contrib.sites.models import Site
from ncs.utils.sendmail import sendmailFromTemplate

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

def sendReminderEmailToRequester(request):
    current_site = Site.objects.get_current()
    template_name = 'papershare/requester_email.txt'
    context = { 'request': request ,
                'site' : current_site}
    subject = "About your requested paper: %s " % request.paper.title
    
    sendmailFromTemplate(template_name = template_name,
                         toAddr = request.requester.email, 
                         subject = subject, 
                         context = context)
