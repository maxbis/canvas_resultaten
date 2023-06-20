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

# userNamesList = ['Waal']

downloads = 'd:\downloads\dl-canvas-20230606-1455'

# Canvas API URL
API_URL = config.get('main', 'host')
# Canvas API key
API_KEY = config.get('main', 'api_key')

# Initialize a new Canvas object
canvas = Canvas(API_URL, API_KEY)

def translate(text):
    translate={ 'ç':'c', 'Ü':'U', 'ü':'u', 'Ç':'C', 'ğ':'g', 'Ğ':'G', 'ı':'i', 'İ':'I', 'ö':'o', 'Ö':'O', 'ş':'s', 'Ş':'S', 'š':'s', 'ć':'c', ' ':'_','+':'-'}
    for char in translate.keys():
        text = text.replace(char, translate[char])
    return text

def checkSubstringsFromList(fullString, lst):
    for item in lst:
        if item in fullString:
            return True
    return False

def downloadAssignment(assignment):
    print("\nAssignemnt: %s (%s)" % (assignment.name, assignment.id) )

    submissions = assignment.get_submissions()

    #submission = assignment.get_submission(user_id)
    allHashes = {}
    for submission in submissions:
        # print("Submission: %s" % (submission.__dict__))

        if (submission.workflow_state == "unsubmitted"):
            continue
    
        try:
            userName = canvas.get_user(submission.user_id).name
        except:
            userName = "*Unknown"

        # if ( userNamesList and checkSubstringsFromList(userName, userNamesList) ):
        #     print(f" -> Downloading {userName}")
        # else:
        #     print(f"Skipping {userName}, not in list.")
        #     continue
            

        today=datetime.datetime.now().replace(tzinfo=None)
        past=submission.submitted_at_date.replace(tzinfo=None)
        diff=today-past     

        path = os.path.join(downloads,userName)
        path = os.path.join(path, assignment.name)
        path = translate(path)
        if not os.path.exists(path):
            print("\nCreate path: %s" % (path) )
            os.makedirs(path)

        numAttachments = len(submission.attachments)


        if (hasattr(submission, 'attachments')):
            for att in submission.attachments:
                page = requests.get(att.url)
                fn = os.path.join(path,att.filename)
                print("\nOrg file: %s" % (fn) )
                fn = translate(fn)
                print("\nCreate file: %s" % (fn) )
                # print("\nCreate content: %s" % (page.text) )
                with open(fn, 'wb') as f:
                    f.write(page.content)

    print()



course = canvas.get_course(8761) # examen portfolio
print(course.name)

assignments = course.get_assignments()

for assignment in assignments:
    print(assignment.id, assignment.name)
    downloadAssignment(assignment)
