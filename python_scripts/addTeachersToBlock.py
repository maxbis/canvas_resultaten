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

# Define your list of teacher emails here
teacher_emails = ['t.monincx@rocva.nl', 's.krachumott@rocva.nl', 'a.vogel@rocva.nl', 'a.vanderlinden1@rocva.nl', 'y.kurt@rocva.nl', 'm.bisschop@rocva.nl', 't.vanderstam@rocva.nl']
teacher_ids=['8904', '8882', '73267', '8887', '8844', '67247', '8889', '8876']
# teacher_ids=['8904']

# Get the course c24-dev 1 tm 12: 10755 12463 12621 12622  - 12623 12624 12625 12626 - 12627 12628 12629 12630
course_id = 14942

for course_id in [ 10755, 12463, 12621, 12622, 12623, 12624, 12625, 12626, 12627, 12628, 12629, 12630 ]:

    try:
        course = canvas.get_course(course_id)
    except exceptions.ResourceDoesNotExist:
        print(f"No course with the ID {course_id} found.")
        exit(1)

    # Iterate over the list of emails
    for id in teacher_ids:
        print(f"Trying to add {id}")
        # Search for users with that email
        user = canvas.get_user(id)

        # If no users found with that email, skip to the next email
        if not user:
            print(f"No user found with the email {email}.")
            continue

        print(user.name)

        # Enroll the teacher in the course
        result=course.enroll_user(user.id, 'TeacherEnrollment', enrollment_state='active')
        print(result)

        print(f"Enrolled {user.name} in {course.name}")
        print()
