# https://canvasapi.readthedocs.io/en/stable/getting-started.html

from html.parser import HTMLParser
from io import StringIO
from canvasapi import Canvas
import configparser

from pprint import pprint
import requests

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
    print(assignment)

    submissions = assignment.get_submissions()

    #submission = assignment.get_submission(user_id)
    allHashes = {}
    for submission in submissions:
        if (hasattr(submission, 'attachments')):
            for att in submission.attachments:
                # pprint(att.__dict__)
                thisHash=0
                if ( att.mime_class == 'file' and att.size>300):
                    page = requests.get(att.url)
                    thisHash = (hash(page.text))
                elif ( att.mime_class == 'pdf' and att.size>300):
                    thisHash = att.size

                # print(att.display_name+" ("+att.mime_class+"): "+str(thisHash))
                if (thisHash != 0):
                    if thisHash in allHashes:
                        print("*** double found, users: ", submission.user_id, att.display_name, allHashes[thisHash])
                    else:
                        allHashes[thisHash] = str(submission.user_id) + " "+att.display_name


# check blok id
course = canvas.get_course(6579)
print(course.name)

# assignment = course.get_assignment(93446)
# checkAssignment(assignment)

assignments = course.get_assignments()

for assignment in assignments:
    print(assignment.id)
    checkAssignment(assignment)


