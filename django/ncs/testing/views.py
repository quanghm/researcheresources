from django.shortcuts import render_to_response
from django.http import HttpResponseRedirect, HttpResponse
from django.contrib import auth
from django.template import RequestContext

from ncs.settings import MEDIA_ROOT
from forms import UploadFileForm

import datetime
import os
from tempfile import mkstemp

def upload_file(request):
    if request.method == 'POST':
        form = UploadFileForm(request.POST, request.FILES)
        if form.is_valid():
            fileName = handle_uploaded_file(request.FILES['file'])
            
            return HttpResponse('You file has been uploaded to %s ' % fileName)
    else:
        form = UploadFileForm()
    return render_to_response('testing/upload.html', {'form': form})


def handle_uploaded_file(f):
    #see tempfile note
    #http://utcc.utoronto.ca/~cks/space/blog/python/UsingTempfile
    fd , fileName = mkstemp(f.name,"uploaded/",MEDIA_ROOT)
    
    destination = os.fdopen(fd, "w+b")
    for chunk in f.chunks():
        destination.write(chunk)
    destination.close()
    return fileName
