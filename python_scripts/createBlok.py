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

course = account.create_course(
    course={
        'name':'Examens - C22',
        'course_code':'LCT-SD-OP11-C22-25604OR',
        'default_view': 'modules',
        'licence': 'private',
    }
)

print(f"Created course with ID: {course.id}")

