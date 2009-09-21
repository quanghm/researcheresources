#!/bin/bash
export DJANGO_SETTINGS_MODULE=ncs.settings
python runner.py ncs/scheduler/schedule.py" >> ncs/media/_ncs_logs/scheduler.log &
