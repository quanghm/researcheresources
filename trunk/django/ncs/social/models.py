from django.db import models
from ncs.papershare.models import Request, RESEARCH_FIELDS
from django.contrib.auth.models import User
from django.contrib import admin
import datetime

# Create your models here.
class Comment(models.Model):
    request = models.ForeignKey(Request, related_name = "request_to_comment")
    timestamp = models.DateTimeField()    
    commenter = models.ForeignKey(User, related_name = "commenter")
    content = models.CharField(max_length = 4096)
    
    def __unicode__(self):
        return "[%d][%s]comment for request %s by %s" % (self.id,`self.timestamp`,`self.request`, self.commenter)        

class Wall(models.Model):
    owner = models.ForeignKey(User, related_name = "owner")
    writer = models.ForeignKey(User, related_name = "wall_writer")
    timestamp = models.DateTimeField()        
    content = models.CharField(max_length = 4096)
    reply_to = models.ForeignKey("self")
    
    def __unicode__(self):
        return "[%d]wall owner=%s, writer=%s, time=%s, replyTo = %s" % (self.id,`self.owner`,`self.writer`,`self.timestamp`,`self.replyTo`)        

class Friendship(models.Model):
    myself = models.ForeignKey(User, related_name = "myself")
    friend = models.ForeignKey(User, related_name = "my_friend")
    timestamp = models.DateTimeField()        
    note = models.CharField(max_length = 128)    
    
    def __unicode__(self):
        return "[%d]I (%s) has a friend, %s, at %s, note = %s" % (self.id,`self.myself`,`self.friend`,`self.timestamp`,`self.note`)        

#extend user with a profile
# see http://www.b-list.org/weblog/2006/jun/06/django-tips-extending-user-model/
class Profile(models.Model):
    # This is the only required field
    user = models.ForeignKey(User, unique=True)
    
    # The rest is completely up to you...
    research_field = models.CharField(max_length=4, choices = RESEARCH_FIELDS);
    is_supplier = models.BooleanField()
    last_assignment = models.DateTimeField(null=True)
    avatar_url = models.CharField(max_length = 256)
    born_year = models.IntegerField() #nam sinh
    work_place = models.CharField(max_length = 256) #noi cong tac
    work_role = models.CharField(max_length = 256) # vi tri cong tac , vd: giam doc
    
    def __unicode__(self):
        return "Profile for %d" % self.user.id

    def is_admin(self):
        return self.user.is_staff
    is_admin.short_description = 'Is Admin?'