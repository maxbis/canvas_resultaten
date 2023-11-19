from canvasapi import Canvas
import configparser
from pprint import pprint

config = configparser.ConfigParser()
if ( not (config.read("../import/canvas.ini") or config.read("canvas.ini"))):
    print()
    dd('Error: canvas.ini not found')


# Canvas API URL
API_URL = config.get('main', 'host')
# Canvas API key
API_KEY = config.get('main', 'api_key')

# Initialize a new Canvas object
canvas = Canvas(API_URL, API_KEY)

course = canvas.get_course(8761) # examen portfolio
print(course.name)

assignment = course.get_assignment(94542)

print(assignment)

submissions = assignment.get_submissions()


#submission = assignment.get_submission(user_id)
count = 0
for submission in submissions:
    count = count + 1
    attachment_count = len(submission.attachments)
    grade = submission.entered_grade
    # print(f"{count}:{submission.user_id} {submission.workflow_state} {attachment_count} {grade}")

    if ( grade == None and attachment_count > 0 ):
       print(f"{count}:{submission.user_id} {submission.workflow_state} {submission.attachments[0].filename} {attachment_count} {grade}")
       # pprint(submission)

    # pprint(submission)
    # submission.edit(submission={'posted_grade':0})
    # submission.edit(submission={'comments':'ivm gepland examen wordt dit niet meer nagegeken.'})

