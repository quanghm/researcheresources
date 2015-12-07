# Introduction #

Chao mung ban tham gia vao du an nghiencuusinh dot org

# Details #

1. Project Management

Project home : http://code.google.com/p/researcheresources/
Svn repo url: https://researcheresources.googlecode.com/svn/trunk/

2. Code Version Control.

NCS.org code duoc quan ly bang SubVersion.
Ban nen download phan mem [TortoiseSVN](http://tortoisesvn.tigris.org/) cho windows de check-out/check-in code. Neu ban lan dau tien su dung SVN, [hay xem huong dan o day](http://www.shokhirev.com/nikolai/programs/SVN/svn.html)

Ban can dang ky 1 gmail account, roi gui email cho project owner de add vao developer list. Sau khi tro thanh project member, ban vao phan [Source](http://code.google.com/p/researcheresources/source/checkout) cua Google Code de generate password cho minh.

4. Install MySQL database
```
sudo yum install mysql-server.x86_64
```
5. Install Python

6. [Install Django](http://docs.djangoproject.com/en/dev/intro/install/)
```
#install MySQLdb 
sudo yum install MySQL-python.x86_64
#install Django
wget http://www.djangoproject.com/download/1.2.3/tarball/
tar -zxvf Django-1.2.3.tar.gz
cd Django-1.2.3
sudo python setup.py install
```

## Check out and build ##

1.
```
mkdir svn
cd svn
[cuong@localhost svn]$ svn checkout https://researcheresources.googlecode.com/s
vn/trunk/django django --username kimcuong 
```