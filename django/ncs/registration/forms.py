"""
Forms and validation code for user registration.

"""

import re

from django import forms
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User

from ncs.registration.models import RegistrationProfile
from ncs.papershare.models import RESEARCH_FIELDS, PaperShareProfile
import re

# I put this on all required fields, because it's easier to pick up
# on them with CSS or JavaScript if they have a class of "required"
# in the HTML. Your mileage may vary. If/when Django ticket #3515
# lands in trunk, this will no longer be necessary.
attrs_dict = { 'class': 'required' }

alnum_re = re.compile(r'^\w+$')

class RegistrationForm(forms.Form):
    """
    Form for registering a new user account.
    
    Validates that the requested username is not already in use, and
    requires the password to be entered twice to catch typos.
    
    Subclasses should feel free to add any additional validation they
    need, but should either preserve the base ``save()`` or implement
    a ``save()`` which accepts the ``profile_callback`` keyword
    argument and passes it through to
    ``RegistrationProfile.objects.create_inactive_user()``.
    
    """
    username = forms.CharField(max_length=30,
                               widget=forms.TextInput(attrs=attrs_dict),
                               label=_(u'username'), help_text = "blah blah")
    email = forms.EmailField(widget=forms.TextInput(attrs=dict(attrs_dict,
                                                               maxlength=75)),
                             label=_(u'email address'))
    password1 = forms.CharField(widget=forms.PasswordInput(attrs=attrs_dict, render_value=False),
                                label=_(u'password'))
    password2 = forms.CharField(widget=forms.PasswordInput(attrs=attrs_dict, render_value=False),
                                label=_(u'password (again)'))
    research_field = forms.ChoiceField(choices=RESEARCH_FIELDS)
    #is_supplier is boolean, but can be replaced by IntegerField sothat RadioSelect can be used 
    is_supplier = forms.IntegerField(widget=forms.RadioSelect(choices=((1,"Yes"),(0,"No"))))
    
    def clean_username(self):
        """
        Validate that the username is alphanumeric and is not already
        in use.
        
        """
        if not alnum_re.search(self.cleaned_data['username']):
            raise forms.ValidationError(_(u'Usernames can only contain letters, numbers and underscores'))
        try:
            user = User.objects.get(username__iexact=self.cleaned_data['username'])
        except User.DoesNotExist:
            return self.cleaned_data['username']
        raise forms.ValidationError(_(u'This username is already taken. Please choose another.'))

    def clean(self):
        """
        Verifiy that the values entered into the two password fields
        match. Note that an error here will end up in
        ``non_field_errors()`` because it doesn't apply to a single
        field.
        
        """
        if 'password1' in self.cleaned_data and 'password2' in self.cleaned_data:
            if self.cleaned_data['password1'] != self.cleaned_data['password2']:
                raise forms.ValidationError(_(u'You must type the same password each time'))
        return self.cleaned_data
    
    def save(self, profile_callback=None):
        """
        Create the new ``User`` and ``RegistrationProfile``, and
        returns the ``User``.
        
        This is essentially a light wrapper around
        ``RegistrationProfile.objects.create_inactive_user()``,
        feeding it the form data and a profile callback (see the
        documentation on ``create_inactive_user()`` for details) if
        supplied.
        
        """
        new_user = RegistrationProfile.objects.create_inactive_user(username=self.cleaned_data['username'],
                                                                    password=self.cleaned_data['password1'],
                                                                    email=self.cleaned_data['email'],
                                                                    research_field = self.cleaned_data['research_field'],
                                                                    is_supplier = self.cleaned_data['is_supplier'],
                                                                    profile_callback=profile_callback)
        return new_user



class RegistrationFormUniqueEmail(RegistrationForm):
    """
    Subclass of ``RegistrationForm`` which enforces uniqueness of
    email addresses.
    
    """
    def clean_email(self):
        """
        Validate that the supplied email address is unique for the
        site.
        
        """
        if User.objects.filter(email__iexact=self.cleaned_data['email']):
            raise forms.ValidationError(_(u'This email address is already in use. Please supply a different email address.'))
        return self.cleaned_data['email']

class RegistrationFormTermsOfService(RegistrationFormUniqueEmail):
    """
    Subclass of ``RegistrationForm`` which adds a required checkbox
    for agreeing to a site's Terms of Service.
    
    """
    tos = forms.BooleanField(widget=forms.CheckboxInput(attrs=attrs_dict),
                             label=_(u'I have read and agree to the Terms of Service'))
    
    def clean_tos(self):
        """
        Validate that the user accepted the Terms of Service.
        
        """
        if self.cleaned_data.get('tos', False):
            return self.cleaned_data['tos']
        raise forms.ValidationError(_(u'You must agree to the terms to register'))


class RegistrationFormNoFreeEmail(RegistrationForm):
    """
    Subclass of ``RegistrationForm`` which disallows registration with
    email addresses from popular free webmail services; moderately
    useful for preventing automated spam registrations.
    
    To change the list of banned domains, subclass this form and
    override the attribute ``bad_domains``.
    
    """
    bad_domains = ['aim.com', 'aol.com', 'email.com', 'gmail.com',
                   'googlemail.com', 'hotmail.com', 'hushmail.com',
                   'msn.com', 'mail.ru', 'mailinator.com', 'live.com']
    
    def clean_email(self):
        """
        Check the supplied email address against a list of known free
        webmail domains.
        
        """
        email_domain = self.cleaned_data['email'].split('@')[1]
        if email_domain in self.bad_domains:
            raise forms.ValidationError(_(u'Registration using free email addresses is prohibited. Please supply a different email address.'))
        return self.cleaned_data['email']

def createAccountSettingFormFromProfileId(userid):
    profile = PaperShareProfile.objects.get(user=userid)
    data = {'profileid' : profile.id,
            'username' : profile.user.username,
            'email' : profile.user.email,
            'password1' : profile.user.password,
            'password2' : profile.user.password,
            'research_field' : profile.research_field,
            'is_supplier' : profile.is_supplier}
    return AccountSettingsForm(data)
    
class AccountSettingsForm(forms.Form):
    """
    """
    profileid = forms.IntegerField(widget=forms.HiddenInput) 
    username = forms.CharField(max_length=30,
                               widget=forms.TextInput(attrs=attrs_dict),
                               label=_(u'username'), help_text = "blah blah")
    email = forms.EmailField(widget=forms.TextInput(attrs=dict(attrs_dict,
                                                               maxlength=75)),
                             label=_(u'email address'))
    password1 = forms.CharField(widget=forms.PasswordInput(attrs=attrs_dict, render_value=True),
                                label=_(u'password'))
    password2 = forms.CharField(widget=forms.PasswordInput(attrs=attrs_dict, render_value=True),
                                label=_(u'password (again)'))
    research_field = forms.ChoiceField(choices=RESEARCH_FIELDS)
    #is_supplier is boolean, but can be replaced by IntegerField sothat RadioSelect can be used 
    is_supplier = forms.IntegerField(widget=forms.RadioSelect(choices=((1,"Yes"),(0,"No"))))
    
    
    def clean_username(self):
        """
        Validate that the username is alphanumeric and is not already
        in use.
        
        """
        if not alnum_re.search(self.cleaned_data['username']):
            raise forms.ValidationError(_(u'Usernames can only contain letters, numbers and underscores'))
        
        return self.cleaned_data['username']

    def clean(self):
        """
        Verifiy that the values entered into the two password fields
        match. Note that an error here will end up in
        ``non_field_errors()`` because it doesn't apply to a single
        field.
        
        """
        if 'password1' in self.cleaned_data and 'password2' in self.cleaned_data:
            if self.cleaned_data['password1'] != self.cleaned_data['password2']:
                raise forms.ValidationError(_(u'You must type the same password each time'))
        return self.cleaned_data
    
    def save(self, profile_callback=None):
        """
        Create the new ``User`` and ``RegistrationProfile``, and
        returns the ``User``.
        
        This is essentially a light wrapper around
        ``RegistrationProfile.objects.create_inactive_user()``,
        feeding it the form data and a profile callback (see the
        documentation on ``create_inactive_user()`` for details) if
        supplied.
        
        """
        profile = PaperShareProfile.objects.get(id=self.cleaned_data['profileid'])
        if self.cleaned_data['username'] != profile.user.username:
            profile.user.username = self.cleaned_data['username']
        if self.cleaned_data['password1'] != profile.user.password:
            profile.user.set_password(self.cleaned_data['password1'])
        if self.cleaned_data['email'] != profile.user.email:
            profile.user.email = self.cleaned_data['email']       
        if self.cleaned_data['research_field'] != profile.research_field:
            profile.research_field = self.cleaned_data['research_field']
        if self.cleaned_data['is_supplier'] != profile.is_supplier:
            profile.is_supplier = self.cleaned_data['is_supplier']
        profile.user.save()
        profile.save()

        
