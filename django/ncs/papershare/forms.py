from django import forms
from django.core.validators import alnum_re
from django.contrib.auth.models import User

from models import Paper, Request, RESEARCH_FIELDS, REQUEST_STATUS_CHOICES
from datetime import datetime

class PaperRequestForm(forms.Form):
    """
    Form for requesting a paper.
    """
    link = forms.URLField()
    title = forms.CharField(max_length = 255, required = False)
    author = forms.CharField(max_length = 255, required = False)
    publisher = forms.CharField(max_length = 255, required = False)
    year = forms.IntegerField(required = False)
    issue = forms.IntegerField(required = False)
    page = forms.IntegerField(required = False)
    research_field = forms.ChoiceField(choices = RESEARCH_FIELDS)
    requester = forms.IntegerField() #this is the hidden field in the form
    
    def clean_requester(self):
        """
        Validate that the requester id is actually a valid user by
        looking up in the database
        """
        try:
            user = User.objects.get(id=self.cleaned_data['requester'])
        except User.DoesNotExist:
            raise forms.ValidationError(_(u'This username is already taken. Please choose another.'))
        return user
        


    def save(self):
        """
        Create a new paper object
        Create a new request object with requester = "the created paper" 
        """
        paper = Paper.objects.create(link=self.cleaned_data['link'],
                                    title=self.cleaned_data['title'],
                                    author=self.cleaned_data['author'],
                                    publisher = self.cleaned_data['publisher'],
                                    year = self.cleaned_data['year'],
                                    issue = self.cleaned_data['issue'],
                                    page = self.cleaned_data['page'],
                                    research_field = self.cleaned_data['research_field'])
        request = Request.objects.create(paper = paper, 
                                         date_requested = datetime.now(),
                                         requester = self.cleaned_data['requester'],
                                         status = 0
                                         )
        return request
    
        
class PaperUploadForm(forms.Form):
    file  = forms.FileField()
    request_id  = forms.IntegerField()
    
    def clean_file(self):
        fileName = self.cleaned_data['file'].name
        pos = fileName.rfind(".")
        if pos != -1 and fileName[pos+1:].lower() in ["pdf","ps","doc"]:
            return fileName
        else:
            raise forms.ValidationError('Only pdf,ps,doc file accepted')
    
    def clean_request_id(self):
        if Request.objects.get(id=self.cleaned_data['request_id']) is None:
            raise forms.ValidationError('Request id is invalid')
        else:
            return self.cleaned_data['request_id']
    
    def save(self, uploaded_url = None):
        "save uploaded file for request_id"
        if uploaded_url is None:
            return
        
        request = Request.objects.get(id=self.cleaned_data['request_id'])
        request.paper.local_link = uploaded_url
        request.status = REQUEST_STATUS_CHOICES[3][0]
        request.paper.save()
        request.save()
        