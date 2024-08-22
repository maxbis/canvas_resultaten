# Get all users created in a particular year (note that these must be assigned to a course)
# then create inserts to insert these users in the database
# use thsi script at the beginning of the year to add students to the CM (klas still needs to be edited by hand)

from canvasapi import Canvas
import configparser
import sys

config = configparser.ConfigParser()
if ( not (config.read("../import/canvas.ini") or config.read("canvas.ini"))):
    print()
    dd('Error: canvas.ini not found')

# Canvas API URL
API_URL = config.get('main', 'host')
# Canvas API key
API_KEY = config.get('main', 'api_key')

canvas = Canvas(API_URL, API_KEY)

course_id = 18660

course = canvas.get_course(course_id)
all_users = course.get_users(enrollment_type=['student'])

print(f"Course Name: {course.name}")

# for user in all_users:
#     if user.created_at[0:4] != '2023':
#     print(user.name, user.id, user.created_at)

# sys.exit()

# Iterate through the list of users
i=1
for user in all_users:
    student_nr = user.sis_user_id[1:]
    email = student_nr + 'talnet.nl'
    # print(f"{i} id: {user.id}, name: {user.name}, login_id: {email} student_nr: {student_nr}")
    print(f"insert into user (id, name, login_id, student_nr, klas) values ('{user.id}', '{user.name}', '{email}', '{student_nr}', '4x');")
    i=i+1

