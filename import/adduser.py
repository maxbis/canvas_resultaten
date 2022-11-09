from canvasapi import Canvas
import configparser
import argparse

config = configparser.ConfigParser()
config.read("canvas.ini")

parser = argparse.ArgumentParser(description='Add user to course')
parser.add_argument('-c', '--courseid', type=int, help='Course id.', required=True)
parser.add_argument('-u', '--userid', type=int, help='(Canvas) User id', required=True)
args = vars(parser.parse_args())


# Canvas API URL
API_URL = config.get('main', 'host')
# Canvas API key
API_KEY = config.get('main', 'api_key')


# Initialize a new Canvas object
canvas = Canvas(API_URL, API_KEY)

course = canvas.get_course(args['courseid'])
print(course.name)


user = canvas.get_user(args['userid'])
print(user.name)

#result=course.enroll_user(args['userid'], 'StudentEnrollment', enrollment_state='active')
#print(result)
