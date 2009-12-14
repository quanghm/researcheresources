#!/usr/bin/env python
# -*- coding: UTF-8 -*-

import datetime,re

from django.db import models
from django.contrib.auth.models import User
from django.contrib import admin
from django.utils.translation import ugettext, ugettext_lazy as _
from django.shortcuts import render_to_response

def strip_html_tags(text):
    return re.sub(r'<[^>]*?>', '', text)

################################################
# Thu vien javascript, css chung cho papershare 
jsloading = ('/media/js/tiny_mce/tiny_mce.js', '/media/js/tiny_mce/textarea.js')

################################################
# Supplier
class Supplier(models.Model):
    class Meta:
        verbose_name_plural = 'Supplier manager'
admin.site.register(Supplier)
################################################
# Annountcement
# Phân loại announcement, 1 loại là thông báo thuần túy thể hiện trên trang chủ
# 1 loại có thể dùng để lưu trữ sẵn nội dung của email.

ANNOUN_TYPES = (
               ('AN', 'Announcement'),
               ('EM', 'Email'),
               )

class Announcement(models.Model):
    content = models.TextField()
    date = models.DateField()
    type = models.CharField(max_length=4, choices = ANNOUN_TYPES, default='AN')
    def __unicode__(self):
        return "Announcement : %s" % strip_html_tags(self.content)[:45]

class AnnouncementAdmin(admin.ModelAdmin):
    def clean_content(self):
        return strip_html_tags(self.content)[:45]

    list_display = (clean_content, 'type', 'date')
    list_filter = ('type', 'date')
    class Media:
        js = jsloading

admin.site.register(Announcement, AnnouncementAdmin)

################################################
# Paper & Request
REQ_STA_PENDING = 0
REQ_STA_ASSIGNED = 1
REQ_STA_REASSIGNED = 2
REQ_STA_SUPPLIED = 3
REQ_STA_THANKED = 4
REQ_STA_FAILED = 5
REQ_STA_LASTCHANCE = 6

REQUEST_STATUS_CHOICES = (
        (REQ_STA_PENDING , "pending"),
        (REQ_STA_ASSIGNED, "assigned"),
        (REQ_STA_REASSIGNED, "re-assigned"),
        (REQ_STA_SUPPLIED, "supplied"),
        (REQ_STA_THANKED, "thanked"),
        (REQ_STA_FAILED, "failed"),
        (REQ_STA_LASTCHANCE, "last-chance"),
    )


RESEARCH_FIELDS = (
        ('BIO', 'Biology'),
        ('CHEM', 'Chemistry'),
        ('CS', 'Computer Science'),
        #('CEE', 'Civil and Environmental Engineering'),
        #('ECON', 'Economics'),
        ('EE', 'Electrical Engineering'),
        #('ENG', 'Engineering - Other'),
        #('ME', 'Mechanical Engineering'),
        ('MATH', 'Mathematics'),
        #('MSE', 'Material Science and Engineering'),
        ('PHYS', 'Physics'),
        #('NONE', 'None of the above'),
    )


class Paper(models.Model):
    link = models.URLField()
    title = models.CharField(max_length = 255)
    author = models.CharField(max_length = 255)
    publisher = models.CharField(max_length = 255)
    year = models.IntegerField()
    issue = models.IntegerField(null=True)
    page = models.IntegerField(null=True)
    research_field = models.CharField(max_length=4, choices = RESEARCH_FIELDS)
    local_link = models.URLField()
    
    def __unicode__(self):
        return "%s" % self.title[:30]
    
class Request(models.Model):
    paper = models.ForeignKey(Paper, related_name = "paper to request")
    date_requested = models.DateTimeField()
    date_assigned = models.DateTimeField(null = True, blank = True)
    date_supplied = models.DateTimeField(null = True, blank = True)
    date_passed = models.DateTimeField(null = True, blank = True)
    requester = models.ForeignKey(User, related_name = "paper requester")
    supplier =  models.ForeignKey(User, related_name = "paper supplier", null = True)
    status = models.SmallIntegerField(choices = REQUEST_STATUS_CHOICES)
    previously_assigned = models.CharField(max_length = 255)
    
    def __unicode__(self):
        if self.status == 0:
            return "[%d][requester=%s] Pending request for paper '%s'" % (self.id,self.requester.username,self.paper.title)
        elif self.status in (1,2):
            return "[%d][requester=%s] Request for paper '%s' assigned to %s" % (self.id,self.requester.username,self.paper.title, self.supplier)
        else:
            return "[%d][requester=%s][status=%d] Request for paper '%s' assigned to %s" % (self.id,self.requester.username,self.status, self.paper.title, self.supplier)

class RequestAdmin(admin.ModelAdmin):
    list_display = ('paper', 'requester', 'date_requested', 'status','supplier')
    list_filter = ('status',)
    date_hierarchy = 'date_requested'
    search_fields = ['paper__title']

admin.site.register(Request,RequestAdmin)


################################################
# Paper
class RequestInline(admin.TabularInline):
    model = Request

class PaperAdmin(admin.ModelAdmin):
    list_display = ('title', 'author', 'publisher', 'research_field')
    list_filter = ('research_field',)
#    date_hierarchy = 'date_requested'
    search_fields = ['title','author']
    inlines = [
        RequestInline,
    ]

admin.site.register(Paper,PaperAdmin)


################################################
# Profile
# extend user with a profile
# see http://www.b-list.org/weblog/2006/jun/06/django-tips-extending-user-model/

class PaperShareProfile(models.Model):
    # This is the only required field
    user = models.ForeignKey(User, unique=True)
    
    # The rest is completely up to you...
    research_field = models.CharField(max_length=4, choices = RESEARCH_FIELDS);
    is_supplier = models.BooleanField()
    last_assignment = models.DateTimeField(null=True)
    
    def __unicode__(self):
        return "Profile for %d" % self.user.id

    def is_admin(self):
        return self.user.is_staff
    is_admin.short_description = 'Is Admin?'

#TODO: what is it?
def paper_share_profile_callback(user, research_field, is_supplier):
    print "--- HAHA"
    profile = PaperShareProfile(user=user, research_field = research_field, is_supplier = is_supplier)
    profile.save()
    
class ProfileAdmin(admin.ModelAdmin):
    list_display = ('user', 'research_field','is_supplier','daysSinceLastLogin')
    list_filter = ('is_supplier','research_field')
    search_fields = ['user__username',]
    def daysSinceLastLogin(self, obj):
        return (datetime.datetime.now() - obj.user.last_login ).days
    daysSinceLastLogin.short_description = 'Days since last login'
    daysSinceLastLogin.admin_order_field = 'user__last_login'
    
admin.site.register(PaperShareProfile,ProfileAdmin)

################################################
# 

class UserAdmin(admin.ModelAdmin):
    list_display = ('username', 'first_name', 'last_name', 'email','is_staff','last_login','date_joined')
    list_filter = ('is_staff',)
#    date_hierarchy = 'date_requested'
    search_fields = ['username','email','first_name','last_name']

#admin.site.register(User,UserAdmin)

#This is to migrate old nghiencuusinh data to new
class TblUser(models.Model):
    id = models.IntegerField(db_column='ID') # Field name made lowercase.
    username = models.CharField(max_length=45, primary_key=True)
    password = models.CharField(max_length=765)
    email = models.CharField(max_length=150)
    field = models.CharField(max_length=150)
    join_date = models.DateField()
    supplier = models.CharField(max_length=1)
    admin = models.CharField(max_length=1)
    user_level = models.IntegerField()
    request_number = models.IntegerField()
    request_handle_number = models.IntegerField()
    request_pending_number = models.IntegerField()
    last_assigned_request = models.DateField()
    class Meta:
        db_table = u'tbl_user'

