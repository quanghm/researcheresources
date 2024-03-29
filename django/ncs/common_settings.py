# Django settings for ncs project.

DEBUG = True
TEMPLATE_DEBUG = DEBUG

ADMINS = (
    ('Cuong', 'kimcuong@gmail.com'),
)

DEFAULT_FROM_EMAIL = '"nghiencuusinh.org"<admin@nghiencuusinh.org>'

MANAGERS = ADMINS





#======================================================================================================

# URL prefix for admin media -- CSS, JavaScript and images. Make sure to use a
# trailing slash.
# Examples: "http://foo.com/media/", "/media/".
ADMIN_MEDIA_PREFIX = '/media/'

# Make this unique, and don't share it with anybody.
SECRET_KEY = '44sq)ql(mc*zv!%1(a(r&hi(3tltr-k#!d7paya-fk6u3yvmpc'

# List of callables that know how to import templates from various sources.
TEMPLATE_LOADERS = (
    'django.template.loaders.filesystem.load_template_source',
    'django.template.loaders.app_directories.load_template_source',
#     'django.template.loaders.eggs.load_template_source',
)

MIDDLEWARE_CLASSES = (
    'django.middleware.common.CommonMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.middleware.doc.XViewMiddleware',
)

ROOT_URLCONF = 'ncs.urls'


INSTALLED_APPS = (
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.sites',
    'django.contrib.admin',
    'ncs.papershare',
    'ncs.registration',
)

#ncs.registration
ACCOUNT_ACTIVATION_DAYS = 30

#for user profile
#see http://www.b-list.org/weblog/2006/jun/06/django-tips-extending-user-model/
AUTH_PROFILE_MODULE = "papershare.PaperShareProfile"

TEMPLATE_CONTEXT_PROCESSORS = (
    "django.core.context_processors.auth",
)

TEMPLATE_STRING_IF_INVALID = "" #"--%s--"

# Local time zone for this installation. Choices can be found here:
# http://en.wikipedia.org/wiki/List_of_tz_zones_by_name
# although not all choices may be available on all operating systems.
# If running in a Windows environment this must be set to the same as your
# system time zone.
TIME_ZONE = 'America/Chicago'

# Language code for this installation. All choices can be found here:
# http://www.i18nguy.com/unicode/language-identifiers.html
LANGUAGE_CODE = 'en-us'

SITE_ID = 1

# If you set this to False, Django will make some optimizations so as not
# to load the internationalization machinery.
USE_I18N = True


LOGIN_URL='/papershare/accounts/login/'


#################################################
## Make sure to set these at each client
WORKING_DIR = "C:/working/nghiencuusinh.org/trunk/django/ncs"

DOMAIN_NAME = 'http://localhost:8000'
SHARE_DIR_ROOT = "C:/working/nghiencuusinh.org"


# URL that handles the media served from MEDIA_ROOT. Make sure to use a
# trailing slash if there is a path component (optional in other cases).
# Examples: "http://media.lawrence.com", "http://example.com/media/"
MEDIA_URL = DOMAIN_NAME + '/papershare/media/'


DATABASE_ENGINE = 'mysql'           # 'postgresql_psycopg2', 'postgresql', 'mysql', 'sqlite3' or 'oracle'.
DATABASE_NAME = 'nghiencuusinh'             # Or path to database file if using sqlite3.
DATABASE_USER = 'root'             # Not used with sqlite3.
DATABASE_PASSWORD = '123456'         # Not used with sqlite3.
DATABASE_HOST = 'localhost'             # Set to empty string for localhost. Not used with sqlite3.
DATABASE_PORT = ''             # Set to empty string for default. Not used with sqlite3.


##################################################
# Dependent
TEMPLATE_DIRS = (
    # Put strings here, like "/home/html/django_templates" or "C:/www/django/templates".
    # Always use forward slashes, even on Windows.
    # Don't forget to use absolute paths, not relative paths.
    WORKING_DIR + '/templates',
)
# Absolute path to the directory that holds media.
# Example: "/home/media/media.lawrence.com/"
# make sure you create a folder "uploaded" and make it world-read-writable for uploaded files
MEDIA_ROOT = WORKING_DIR + '/media'
SHARE_DIR_URL = DOMAIN_NAME + '/static/'
FILE_UPLOAD_TEMP_DIR = MEDIA_ROOT + '/uploaded'
LOG_FILE = MEDIA_ROOT + "/logs/django.log"
