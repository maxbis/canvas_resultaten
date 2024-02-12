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

COURSE_IDS = ["10755", "12463", "12621", "12622", "12623", "12624", "12625", "12626", "12627", "12628", "12629"]  # Replace these with your actual course IDs

for course_id in COURSE_IDS:
    try:
        # Get the course using the current course ID from the array
        course = canvas.get_course(course_id)

        # Update the course to make it public
        course.update(course={'is_public': True})
        
        print(f"Course {course_id} visibility updated to public.")

    except Exception as e:
        print(f"Failed to update course {course_id} visibility: {e}")