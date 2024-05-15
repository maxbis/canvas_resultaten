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
API_URL = config.get("main", "host")
# Canvas API key
API_KEY = config.get("main", "api_key")

# Initialize a new Canvas object
canvas = Canvas(API_URL, API_KEY)


courses_assignments = {
    14895: {  # Course ID 1 c21
        209778: "2024-05-19 23:59",
        209779: "2024-05-19 23:59",
        209780: "2024-05-19 23:59",
        209781: "2024-05-19 23:59",
        209782: "2024-05-19 23:59",
        209784: "2024-05-19 23:59",
        209785: "2024-05-19 23:59",
        209786: "2024-05-19 23:59",
    },
    14156: {  # Course ID 2 C22
        189567: "2024-05-19 23:59",
        189568: "2024-04-19 23:59",
        189569: "2024-04-19 23:59",
        189570: "2024-04-19 23:59",
        189571: "2024-04-19 23:59",
        189573: "2024-05-19 23:59",
        189574: "2024-04-19 23:59",
        189575: "2024-04-19 23:59",
    },
}


# Function to update assignment deadlines
def update_assignment_deadlines(courses_assignments):
    for course_id, assignments in courses_assignments.items():
        # Get the course object
        course = canvas.get_course(course_id)

        for assignment_id, new_deadline in assignments.items():
            # Get the assignment object
            assignment = course.get_assignment(assignment_id)

            # Parse the new deadline string into a datetime object
            new_deadline_dt = datetime.datetime.strptime(new_deadline, "%Y-%m-%d %H:%M")

            # Update the assignment deadline
            updated_assignment = assignment.edit(
                assignment={"lock_at": new_deadline_dt}
            )
            print(
                f"Course {course_id} - Updated Assignment {updated_assignment.id}: New Deadline {updated_assignment.lock_at}"
            )


# Run the update function
update_assignment_deadlines(courses_assignments)
