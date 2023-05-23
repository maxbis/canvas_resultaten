#! /usr/bin/python3
# v2251

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
if ( not (config.read("../import/canvas.ini") or config.read("canvas.ini"))):
    print()
    dd('Error: canvas.ini not found')

parser = argparse.ArgumentParser(description='Add user to course')
parser.add_argument('-b', '--blok', type=str, help='Blok (cursus) Name or Id', required=True)
parser.add_argument('-s', '--student', type=str, help='Student Name or Id', required=True)
parser.add_argument('-x', '--execute', action='store_true', help='Execute', required=False)
parser.add_argument('-d', '--database', type=str, help='Database name, if ommited, the name from the ini file will be used', default='database', required=False)
args = vars(parser.parse_args())


# Canvas API URL
API_URL = config.get('main', 'host')
# Canvas API key
API_KEY = config.get('main', 'api_key')

try:
    databaseSectionName=args['database']
    dbName = config.get(databaseSectionName, 'db')
    dbUser = config.get(databaseSectionName, 'user')
    dbPassword = config.get(databaseSectionName, 'password')
except:
    dd('Error reading canvas.ini database parameters')


class CanvasDb:

    def __init__(self, dbName, user, passwd):
        self.dbName=dbName
        self.user=user
        self.passwd=passwd
        self.cursor=''
        self.connect()

    def connect(self):
        try:
            self.con = pymysql.connect(host='localhost', user=dbUser, passwd=dbPassword, db=dbName, client_flag=CLIENT.MULTI_STATEMENTS)
            self.cursor = self.con.cursor()
            print("Connected to the database successfully!")
        except Exception as e:
            print(f"Error connecting to the database: {e}")

    def courses(self,searchString='B'):
        sql="select id, naam from course where naam like '%"+str(searchString)+"%' or korte_naam = '"+str(searchString)+"%' or id='"+str(searchString)+"';"
        self.cursor.execute(sql)
        courses = self.cursor.fetchall()
        if len(courses)==0:
           print(f"No Canvas courses (blocks) found")
           return None
        
        return courses

    def teachers(self, searchString='Bisschop'):
        sql="select id, name from user where name like '%"+str(searchString)+"%' or id='"+str(searchString)+"';"
        self.cursor.execute(sql)
        teachers = self.cursor.fetchall()
        if len(teachers)==0:
           print(f"No Teacher found")
           return None
        if len(teachers)!=1:
            for teacher in teachers:
                print('Found %s with id %s' % (teacher[1], teacher[0]))
            print(f" *** More names found, be more specific! ***")
            return None

        return teachers[0]


courseName = args['blok']
userName= args['student']

canvasDb = CanvasDb(dbName, dbUser, dbPassword)
courses = canvasDb.courses(courseName)
teacher = canvasDb.teachers(userName)

if not teacher or not courses:
    sys.exit()

print('\nAssigning %s with id %s to:' % (teacher[1], teacher[0]))

for course in courses:
        print('  %s with id %s' % (course[1], course[0]))

        

print("\nstarting Canvas API update");

# Initialize a new Canvas object
canvas = Canvas(API_URL, API_KEY)

for course in courses:

    thisCourse = canvas.get_course(course[0])
    print(thisCourse.name)

    thisUser = canvas.get_user(teacher[0])
    print(thisUser.name)

    if args['execute']:
        print("Execute")
        result=thisCourse.enroll_user(thisUser, 'TeacherEnrollment', enrollment_state='active')
        print(result)
    else:
        print("No execution, use -x")
