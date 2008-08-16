from ncs.papershare.models import Request
from django.contrib.auth.models import User

from ncs.papershare.models import PaperShareProfile
from ncs.papershare.models import REQUEST_STATUS_CHOICES
from datetime import datetime

def findSupplier(request):
    try:    
        supplierProfile = PaperShareProfile.objects.filter(is_supplier=True,research_field=request.paper.research_field).order_by('last_assignment')[0]
    except IndexError:
        print "!!!!! Field %s doesn't have any supplier [%s]" % (request.paper.research_field, request)
        return
    
    request.supplier = supplierProfile.user
    last_assignment = supplierProfile.last_assignment
    
    t = max(datetime.now() , last_assignment) 
    supplierProfile.last_assignment = datetime(t.year, t.month, t.day, t.hour, t.minute + 1, t.second) #make sure the user will not get request until next minute  
    supplierProfile.save()
     
    request.date_assigned = t
    if request.previously_assigned is None:
        request.status = REQUEST_STATUS_CHOICES[1][0] #"assigned"
    else:
        request.status = REQUEST_STATUS_CHOICES[2][0] #"re-assigned"
    request.save()
    print "Request %s (%s) is assigned to user %s(%s)" % (unicode(request), 
                                                          request.date_requested, 
                                                          unicode(supplierProfile.user),
                                                          last_assignment)
    
def main(argv):
    requestQueue = Request.objects.filter(status__exact=0).order_by('date_requested')
    for request in requestQueue:
        findSupplier(request)
    print "Running request scheduler"