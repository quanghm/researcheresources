from django import forms
from django.contrib.auth.models import User

from datetime import datetime


class UploadFileForm(forms.Form):
    title = forms.CharField(max_length=50)
    file  = forms.FileField()