# Get all users created in a particular year (note that these must be assigned to a course)
# then create inserts to insert these users in the database
# use thsi script at the beginning of the year to add students to the CM (klas still needs to be edited by hand)

from canvasapi import Canvas
import configparser

config = configparser.ConfigParser()
if ( not (config.read("../import/canvas.ini") or config.read("canvas.ini"))):
    print()
    dd('Error: canvas.ini not found')

# Canvas API URL
API_URL = config.get('main', 'host')
# Canvas API key
API_KEY = config.get('main', 'api_key')

canvas = Canvas(API_URL, API_KEY)

account = canvas.get_account(82)

all_users = account.get_users()

# Iterate through the list of users
i=1
for user in all_users:
    if user.created_at[0:4] == '2023':
        parts = user.login_id.split('@')
        student_nr = parts[0]
        # print(f"{i} id: {user.id}, name: {user.name}, login_id: {user.login_id} student_nr: {student_nr}")
        print(f"insert into user (id, name, login_id, student_nr) values ('{user.id}', '{user.name}', '{user.login_id}', '{student_nr}');")
        i=i+1

