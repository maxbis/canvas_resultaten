# Script to give a student all marks for a complete course
# fill in course_id, Full studentn name, and Name of assignment group.


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



# Replace with the course ID
course_id = 10755

# Replace with the full name of the student you want to rate
student_full_name = 'Lucas Justus'

# Replace with the name of the assignment group you want to target
target_assignment_group_name = 'Introductie'



# Get the course object
course = canvas.get_course(course_id)

# Find the student by full name
students = course.get_users(enrollment_type=['student'])
target_student = None

for student in students:
    if student.short_name == student_full_name:
        target_student = student
        break

if target_student is None:
    print(f"Student '{student_full_name}' not found.")
else:
    print(f"Found {student.short_name}")

    # Get the target student's ID
    student_id = target_student.id

    # Retrieve all assignments for the course
    assignments = course.get_assignments()

    # Find the assignment group with the specified name
    target_assignment_group = None
    for group in course.get_assignment_groups():
        if group.name == target_assignment_group_name:
            target_assignment_group = group
            break

    if target_assignment_group is None:
        print(f"Assignment group '{target_assignment_group_name}' not found.")
    else:
        print(f"Found Assignment  '{target_assignment_group_name}'")
        # Iterate through the assignments and set the student's grade to the maximum possible points
        total = 0
        for assignment in assignments:
            if assignment.assignment_group_id == target_assignment_group.id:
                rating = int(assignment.points_possible - 1)
                total += rating
                print(f"About to rate {assignment.name} with { rating }")
                #  assignment.edit_submission(submission_type='', body={'grade_data': {'text_comment': 'Auto Excused'}, 'posted_grade': rating })
        print(f"Total points given: {total}")