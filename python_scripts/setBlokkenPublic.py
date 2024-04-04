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


for course_id in [ 10755, 12463, 12621, 12622, 12623, 12624, 12625, 12626, 12627, 12628, 12629, 12630 ]:
    course = canvas.get_course(course_id)
    course.update(course={'is_public': False})



