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
        print("\nAssignemnt: %s (%s) %s %s %s" % (assignment.name, assignment.id, assignment.allowed_attempts, assignment.grader_count, assignment.points_possible) )
        submissions = assignment.get_submissions()
        for submission in submissions:
            if submission.attempt and (int(submission.attempt)>3 and int(submission.score)>int(int(assignment.points_possible)*0.8)) and not submission.external_tool_url:
                print("Max: %d, Attempt: %d, Score: %d, new Max: %d" % ( assignment.points_possible ,submission.attempt ,submission.score, int(int(assignment.points_possible)*0.8) ) )
                print(submission.preview_url)
                print(course, assignment.id)
                print('----')

        # assignment.edit( assignment = { 'allowed_attempts' : 6 } )
        # print(" - allowed attempts changed in %s" % (assignment.allowed_attempts) )

# c22 blok 1 t/m 8
# 7757 6579 6580 6581 6582 6585 7760 7761

# c21 blok 2 t/m 10 (gedaan)
# 2110 3237 3238 3239 4999 5429 6450 

course = canvas.get_course(6581)
# type_list = ['student']
# users = course.get_users(enrollment_type=type_list)
# for user in users:
#     print(user.name, user.id)

print("Begin")
setAttempts(course)


