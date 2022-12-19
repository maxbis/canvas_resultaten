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


def setAttempts(course):
    assignments = course.get_assignments()
    for assignment in assignments:
        print("\nAssignemnt: %s (%s) %s" % (assignment.name, assignment.id, assignment.allowed_attempts) )
        assignment.edit( assignment = { 'allowed_attempts' : 6 } )


        
# check blok id
course = canvas.get_course(7757)
# course = canvas.get_course(6580) # blok 2 c22
# course = canvas.get_course(4999) # blok 6 c21
# course = canvas.get_course(6586) # blok 9/10 c21

# course = canvas.get_course(6580) # blok 3 c22

course = canvas.get_course(7757)
setAttempts(course)


