from canvasapi import Canvas
import configparser
from pprint import pprint
import sys

# Grade all submitted submissions (workflow_state == submitted and workflow_state != graded)

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

assignment = course.get_assignment(94559) # ass. id
print(assignment)

submissions = assignment.get_submissions()


#submission = assignment.get_submission(user_id)
count = 0
for submission in submissions:
    count = count + 1
    attachment_count = len(submission.attachments)
    workflow_state = submission.workflow_state
    # print(f"{count}:{submission.user_id} {submission.workflow_state} {attachment_count} {grade}")

    if ( workflow_state == 'submitted' ):
       print(f"Grading: {count}:{submission.user_id} {submission.workflow_state} fn:{submission.attachments[0].filename} att:{attachment_count} grade:{submission.grade}")
       submission.edit(submission={"posted_grade": str(0)})
       submission.edit(comment={"text_comment": 'Niet (hier) nagekeken ivm examen', "attempt": submission.attempt}) #  We have to set the attempt, otherwise it will be none and will be reagerded as 1 (1ste attempt).
       #sys.exit('Done update one record')
    else:
       print(f"Already Graded: {count}:{submission.user_id} {submission.workflow_state} att:{attachment_count} grade:{submission.grade}")


