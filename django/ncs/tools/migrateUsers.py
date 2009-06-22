import sys

from django.contrib.auth.models import User
from ncs.papershare.models import PaperShareProfile, TblUser


def listUsersFromOldTable():
    users = TblUser.objects.all()
    countField = {}
    for user in users:
        field = user.field
        if not countField.has_key(field):
            countField[field] = 0
        countField[field] += 1;
    
    print "Total users ", len(users)
    for (field,count) in countField.items():
        print count, " users found in ", field, " field."
    print "----------------------------"

def migrateUsers():
    for olduser in TblUser.objects.all():
        print olduser.id,
        print "Migrating user", olduser.username
        if User.objects.filter(username=olduser.username).count() > 0:
            print "User ", olduser.username, " is existed"
            continue
        if User.objects.filter(email=olduser.email).count() > 0:
            print "Email ", olduser.email, " is existed"
            continue
        user = User()
        user.username = olduser.username
        user.email = olduser.email
        user.is_active = 1
        user.date_joined = olduser.join_date
        user.save()
        userProfile = PaperShareProfile()        
        userProfile.user = user
        userProfile.research_field = olduser.field
        userProfile.is_supplier = olduser.supplier        
        userProfile.save()
        #break

def main(argv):
    option = "list"
    for arg in argv:
        if arg == "--migrate":
            option = "migrate"
            
    if option == "list":
        listUsersFromOldTable()
    elif option == "migrate":
        migrateUsers()
    
if __name__ == "__main__" : 
    main(sys.argv)