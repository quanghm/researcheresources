from django.db import models
from django.db import models
from django.contrib.auth.models import User


class Announcement(models.Model):
    content = models.TextField()
    date = models.DateField()

#extend user with a profile
# see http://www.b-list.org/weblog/2006/jun/06/django-tips-extending-user-model/
RESEARCH_FIELDS = (
        ('BIO', 'Biology'),
        ('CHEM', 'Chemistry'),
        ('EE', 'Electrical Engineering'),
        ('ME', 'Mechanical Engineering'),
        ('MATH', 'Mathematics'),
        ('PHYS', 'Physics'),
        ('NONE', 'None of the above'),
    )
class PaperShareProfile(models.Model):
    # This is the only required field
    user = models.ForeignKey(User, unique=True)

    
    # The rest is completely up to you...
    research_field = models.CharField(max_length=4, choices = RESEARCH_FIELDS);
    is_supplier = models.BooleanField()

def paper_share_profile_callback(user, research_field, is_supplier):
    print "--- HAHA"
    profile = PaperShareProfile(user=user, research_field = research_field, is_supplier = is_supplier)
    profile.save()
    