#! /usr/bin/python3

from canvasapi import Canvas
import configparser
import argparse
import pymysql
from pymysql.constants import CLIENT
import sys

# Debug-dump and die
def dd(arg):
    try:
        print(arg)
    except:
        pprint(arg)
    sys.exit()



config = configparser.ConfigParser()
config.read("canvas.ini")

parser = argparse.ArgumentParser(description='Add user to course')
parser.add_argument('-b', '--blok', type=str, help='Blok (cursus) Name or Id', required=True)
parser.add_argument('-s', '--student', type=str, help='Student Name or Id', required=True)
parser.add_argument('-x', '--execute', action='store_true', help='Execute', required=False)
args = vars(parser.parse_args())


# Canvas API URL
API_URL = config.get('main', 'host')
# Canvas API key
API_KEY = config.get('main', 'api_key')

try:
    dbName = config.get('database', 'db')
    dbUser = config.get('database', 'user')
    dbPassword = config.get('database', 'password')
except:
    dd('Error reading canvas.ini database parameters')

# connect to MySQL
try:
    con = pymysql.connect(host='localhost', user=dbUser, passwd=dbPassword, db=dbName, client_flag=CLIENT.MULTI_STATEMENTS)
    cursor = con.cursor()
except:
    dd('Error: cannot connect to database '+dbName)

courseName = args['blok']
studentName= args['student']

sql="select id, naam from course where naam like '%"+str(courseName)+"%' or korte_naam = '"+str(courseName)+"' or id='"+str(courseName)+"';"
cursor.execute(sql)
rows = cursor.fetchall()

if len(rows)!=1:
    print('Found more than one course, be more specific!')
    for row in rows:
        print(row[0], row[1])
    sys.exit()

print("Found course %s with id %s" % (rows[0][1], rows[0][0]) )
thisCourseId=rows[0][0]


sql="select id, name from user where name like '%"+str(studentName)+"%' or id='"+str(studentName)+"';"
cursor.execute(sql)
rows = cursor.fetchall()

if len(rows)!=1:
    print('Found more than one student, be more specific!')
    for row in rows:
        print(row[0], row[1])
    sys.exit()

print("Found student %s with id %s" % (rows[0][1], rows[0][0]) )
thisStudentId=rows[0][0]


print("starting Canvas API update");

# Initialize a new Canvas object
canvas = Canvas(API_URL, API_KEY)

course = canvas.get_course(thisCourseId)
print(course.name)

user = canvas.get_user(thisStudentId)
print(user.name)


if args['execute']:
    print("Execute")
    result=course.enroll_user(thisStudentId, 'StudentEnrollment', enrollment_state='active')
    print(result)
else:
    print("No execution, use -x")
