from django.db import models
from django.db import models
from django.contrib.auth.models import User
from django.contrib import admin

class Announcement(models.Model):
    content = models.TextField()
    date = models.DateField()
    def __unicode__(self):
        return "Announcement : %s" % self.content[:45]
    
RESEARCH_FIELDS = (
        ('BIO', 'Biology'),
        ('CHEM', 'Chemistry'),
        ('CS', 'Computer Science'),
        ('EE', 'Electrical Engineering'),
        ('ME', 'Mechanical Engineering'),
        ('MATH', 'Mathematics'),
        ('PHYS', 'Physics'),
        ('NONE', 'None of the above'),
    )

class Paper(models.Model):
    link = models.URLField()
    title = models.CharField(max_length = 255)
    author = models.CharField(max_length = 255)
    publisher = models.CharField(max_length = 255)
    year = models.IntegerField()
    issue = models.IntegerField(null=True)
    page = models.IntegerField(null=True)
    research_field = models.CharField(max_length=4, choices = RESEARCH_FIELDS);
    local_link = models.URLField()
    
    def __unicode__(self):
        return "%s" % self.title[:30]
    
REQUEST_STATUS_CHOICES = (
        (0, "pending"),
        (1, "assigned"),
        (2, "re-assigned"),
        (3, "supplied"),
        (4, "thanked"),
        (5, "failed"),
    )

class Request(models.Model):
    paper = models.ForeignKey(Paper, related_name = "paper to request")
    date_requested = models.DateTimeField()
    date_assigned = models.DateTimeField(null = True)
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

#extend user with a profile
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
    
admin.site.register(Announcement)

class ProfileAdmin(admin.ModelAdmin):
    list_display = ('user', 'research_field','is_supplier')
    list_filter = ('is_supplier',)
    search_fields = ['user__username',]
    
admin.site.register(PaperShareProfile,ProfileAdmin)

class RequestAdmin(admin.ModelAdmin):
    list_display = ('paper', 'requester', 'date_requested', 'status','supplier')
    list_filter = ('status',)
    date_hierarchy = 'date_requested'
    search_fields = ['paper__title']

admin.site.register(Request,RequestAdmin)

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

