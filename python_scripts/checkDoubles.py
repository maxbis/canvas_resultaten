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

def log(line):
    with open('./output.log', 'a', encoding='utf-8') as logfile:
        logfile.write(line + '\n')
    print("log: " + line)

def checkAssignment(assignment):
    log("\nAssignment: %s (%s)" % (assignment.name, assignment.id) )

    submissions = assignment.get_submissions()

    #submission = assignment.get_submission(user_id)
    allHashes = {}
    for submission in submissions:
        # print("Submission: %s" % (submission.__dict__))

        if (submission.workflow_state == "unsubmitted"):
            continue

        today=datetime.datetime.now().replace(tzinfo=None)
        try:
            past=submission.submitted_at_date.replace(tzinfo=None)
            diff=today-past    
            diff_days = diff.days 
        except:
            diff_days = 0 

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
                        log(double)
                    else:
                        allHashes[thisHash] = str(submission.user_id) + " "+att.display_name
                        if ( double == ""):
                            double="OK"

        # print("%30s - %2s (checked: %2s) %10s - %5s - %3s days-  %14s %s" % (userName, numAttachments, checked, extentions, submission.entered_score, diff.days ,submission.workflow_state, double))
        output_line = "%30s - %2s (checked: %2s) %10s - %5s - %3s days-  %14s %s" % (userName, numAttachments, checked, extentions, submission.entered_score, diff_days, submission.workflow_state, double)
        print(output_line)
    print()

# check blok id
course = canvas.get_course(12623) # blok 5 c23

log(course.name)

assignments = course.get_assignments()

for assignment in assignments:
    # log("Assignment Id: %d Assignment Name: %s" % (assignment.id, assignment.name) )git pull

    if ( True or assignment.name.lower().find('-') >= 0 ):
        checkAssignment(assignment)