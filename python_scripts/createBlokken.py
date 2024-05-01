from canvasapi import Canvas
import configparser

# Load configuration
config = configparser.ConfigParser()
if not (config.read("../import/canvas.ini") or config.read("canvas.ini")):
    print('Error: canvas.ini not found')
    exit()

# Canvas API URL and API key
API_URL = config.get('main', 'host')
API_KEY = config.get('main', 'api_key')

# Initialize Canvas object
canvas = Canvas(API_URL, API_KEY)

# Specify the account ID
account = canvas.get_account(82)

# Array containing course data
courses_data = [
    {
        'name': 'Introductie',
        'course_code': 'LCTSD-Introductie',
        'default_view': 'modules',
        'license': 'private'
    },
    {
        'name': 'Programmeren 1',
        'course_code': 'LCTSD-Programmeren_1',
        'default_view': 'modules',
        'license': 'private'
    },
    {
        'name': 'Programmeren 2',
        'course_code': 'LCTSD-Programmeren_2',
        'default_view': 'modules',
        'license': 'private'
    },
    {
        'name': 'Programmeren 3',
        'course_code': 'LCTSD-Programmeren_3',
        'default_view': 'modules',
        'license': 'private'
    },
    {
        'name': 'Programmeren 4',
        'course_code': 'LCTSD-Programmeren_4',
        'default_view': 'modules',
        'license': 'private'
    },
    {
        'name': 'Front End 1',
        'course_code': 'LCTSD-Front_End_1',
        'default_view': 'modules',
        'license': 'private'
    },
        {
        'name': 'Front End 2',
        'course_code': 'LCTSD-Front_End_2',
        'default_view': 'modules',
        'license': 'private'
    },
    {
        'name': 'Databases',
        'course_code': 'LCTSD-Databases',
        'default_view': 'modules',
        'license': 'private'
    },
    {
        'name': 'PHP Frameworks',
        'course_code': 'LCTSD-PHP_Frameworks',
        'default_view': 'modules',
        'license': 'private'
    },
    {
        'name': 'Kerntaken',
        'course_code': 'LCTSD-Kerntaken',
        'default_view': 'modules',
        'license': 'private'
    },
        {
        'name': 'Tools',
        'course_code': 'LCTSD-Programmeren3',
        'default_view': 'modules',
        'license': 'private'
    },

]

# Create courses using the data from the array
for course_info in courses_data:
    course = account.create_course(course=course_info)
    print(f"Created course with ID: {course.id} - Name: {course_info['name']}")
