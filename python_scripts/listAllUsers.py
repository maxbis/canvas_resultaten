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

# The account ID to filter by
account_id = 82

# Get the account object
account = canvas.get_account(account_id)

# List of teacher user IDs across account-specific courses
all_teachers = []

# Iterate through all courses under the specific account
for course in account.get_courses():
    # Get users in the current course
    users = course.get_users(enrollment_type=['teacher'])

    # Collect their IDs and names
    for user in users:
        print(f"{course.id}, {course.name},  {user.id}, {user.name}")
