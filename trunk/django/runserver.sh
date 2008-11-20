nohup python manage.py runserver warbler.cs.uiuc.edu:8000 > media/_ncs_logs/runserver.log &
nohup python manage.py runserver >& media/_ncs_logs/django.log &
