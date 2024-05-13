# https://canvasapi.readthedocs.io/en/stable/getting-started.html

from html.parser import HTMLParser
from io import StringIO
from canvasapi import Canvas
import configparser

from pprint import pprint
import requests

import os.path
import datetime

config = configparser.ConfigParser()
config.read("canvas.ini")


# Canvas API URL
API_URL = config.get('main', 'host')
# Canvas API key
API_KEY = config.get('main', 'api_key')

# Initialize a new Canvas object
canvas = Canvas(API_URL, API_KEY)

lock_date = '2024-05-13T10:00:00Z'


def setAttempts(course):
    assignments = course.get_assignments()
    for assignment in assignments:
        print("\nAssignemnt: %s (%s) %s" % (assignment.name, assignment.id, assignment.allowed_attempts) )
        assignment.edit( assignment = { 'allowed_attempts' : 3 } )
        print(" - allowed attempts changed in %s" % (assignment.allowed_attempts) )
        assignment.edit(assignment={'lock_at': lock_date})
        print(f"Locked {assignment.name} until {lock_date}")


# c22 blok 1 t/m 8
# 7757 6579 6580 6581 6582 6585 7760 7761

# c21 blok 2 t/m 10 (gedaan)
# 2110 3237 3238 3239 4999 5429 6450 

course = canvas.get_course(2110)
setAttempts(course)


