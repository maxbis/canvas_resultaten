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


class MLStripper(HTMLParser):
    def __init__(self):
        super().__init__()
        self.reset()
        self.strict = False
        self.convert_charrefs = True
        self.text = StringIO()

    def handle_data(self, d):
        self.text.write(d)

    def get_data(self):
        return self.text.getvalue()


def strip_tags(html):
    s = MLStripper()
    s.feed(html)
    return s.get_data()


# Canvas API URL
API_URL = config.get('main', 'host')
# Canvas API key
API_KEY = config.get('main', 'api_key')

# Initialize a new Canvas object
canvas = Canvas(API_URL, API_KEY)


def checkAssignment(assignment):
    print("\nAssignemnt: %s (%s)" % (assignment.name, assignment.id) )

    submissions = assignment.get_submissions()

    #submission = assignment.get_submission(user_id)
    allHashes = {}
    for submission in submissions:
        # print("Submission: %s" % (submission.__dict__))

        if (submission.workflow_state == "unsubmitted"):
            continue

        today=datetime.datetime.now().replace(tzinfo=None)
        past=submission.submitted_at_date.replace(tzinfo=None)
        diff=today-past     

        try:
            userName = canvas.get_user(submission.user_id).name
        except:
            userName = "*Unknown* (deleted?)"

        numAttachments = len(submission.attachments)


        if (hasattr(submission, 'attachments')):
            checked=0
            double=""
            extentions=[]
            for att in submission.attachments:
                # pprint(att.__dict__)
                thisExtention=os.path.splitext(att.filename)[1]
                extentions.append( thisExtention )
                
                thisHash=0
                if ( thisExtention in ['.php','.html','.txt', '.sql','.pdf','.png','.jpg','.jpeg','.js'] and att.size > 500):
                    thisHash = att.size
                if ( thisExtention in ['.php','.html','.txt', '.sql','.png'] and att.size > 200):
                    page = requests.get(att.url)
                    thisHash = (hash(page.text))

                if (thisHash != 0):
                    checked+=1
                    if thisHash in allHashes:
                        user1 = canvas.get_user(submission.user_id).name
                        attachment1 = att.display_name
                        user2 = canvas.get_user( allHashes[thisHash].split()[0] ).name
                        attachment2 = allHashes[thisHash].split()[1]
                        double = (" *** double  *** %s %s and %s %s (size: %s bytes)" % (user1, attachment1, user2, attachment2, att.size))
                    else:
                        allHashes[thisHash] = str(submission.user_id) + " "+att.display_name
                        if ( double == ""):
                            double="OK"

        print("%30s - %2s (checked: %2s) %10s - %5s - %3s days-  %14s %s" % (userName, numAttachments, checked, extentions, submission.entered_score, diff.days ,submission.workflow_state, double))
    print()

# check blok id
course = canvas.get_course(6586)
# course = canvas.get_course(6580) # blok 2 c22
# course = canvas.get_course(4999) # blok 6 c21
# course = canvas.get_course(6586) # blok 9/10 c21

# course = canvas.get_course(6580) # blok 3 c22


print(course.name)

# assignment = course.get_assignment(93446)
# checkAssignment(assignment)

assignments = course.get_assignments()

for assignment in assignments:
    print(assignment.id, assignment.name)
    # if ( assignment.name.lower().find('functies') >= 0 ):
    checkAssignment(assignment)


