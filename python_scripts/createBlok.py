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
        'name':'Course Kerntaak-1 intro - C23',
        'course_code':'LCT-SD-C23-25604OR-KT1Intro',
        'default_view': 'modules',
        'licence': 'private',
    }
)

print(f"Created course with ID: {course.id}")

