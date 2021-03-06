# https://canvasapi.readthedocs.io/en/stable/getting-started.html

from canvasapi import Canvas
import configparser

config = configparser.ConfigParser()
config.read("canvas.ini")

from io import StringIO
from html.parser import HTMLParser

class MLStripper(HTMLParser):
    def __init__(self):
        super().__init__()
        self.reset()
        self.strict = False
        self.convert_charrefs= True
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
    fileSize={}
    for submission in submissions:

        if (hasattr(submission,'attachments')):
            for att in submission.attachments:
                # print(submission.user_id, att['size'], att['display_name'], att['content-type'])
                if att['size'] in fileSize:
                    print("*** double found, users: ", submission.user_id, fileSize[att['size']])
                else:
                    fileSize[att['size']]=submission.user_id

course = canvas.get_course(3238)
print(course.name)

assignments = course.get_assignments()

for assignment in assignments:
    print(assignment.id)
    checkAssignment(assignment)

# assignment = course.get_assignment(24210)
# checkAssignment(assignment)
