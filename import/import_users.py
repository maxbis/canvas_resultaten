#! /usr/bin/python3

# Script derived from main import script (2022) - this outputs SQL to update all users belonging to an course.
# course number: see second last line ( updateUsers(course_id) )

import pymysql
import json
import urllib.request
import argparse

import configparser
import sys

from pprint import pprint
from pymysql.constants import CLIENT
from threading import Thread

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

try:
    baseUrl  = config.get('main', 'baseUrl')
    paramUrl = config.get('main', 'paramUrl')
    api_key  = config.get('main', 'api_key')
except:
    dd('Error reading canvas.ini main parameters')

parser = argparse.ArgumentParser(description='Update Users in Canvas Monitor based on users participating in course')
parser.add_argument('-c', '--course', type=int, help='Update course id or update all courses with a specified prio smaller than specified.', required=False)
parser.add_argument('--database', type=str, help='Database name, if ommited, the name from the ini file will be used', default='database', required=False)
args = vars(parser.parse_args())

try:
    databaseSectionName=args['database']
    dbName = config.get(databaseSectionName, 'db')
    dbUser = config.get(databaseSectionName, 'user')
    dbPassword = config.get(databaseSectionName, 'password')
except:
    dd('Error reading canvas.ini database parameters')

prio = args['course']


#### FUNCTIONS ####

def getJsonData(url, courseId):
    pageNr = 1
    json_obj = {}

    url = slashJoin(baseUrl, str(courseId), url, paramUrl)
    thisUrl = url+str(pageNr)

    request = urllib.request.Request(thisUrl)
    request.add_header("Authorization", "Bearer "+api_key)

    try:
        page = urllib.request.urlopen(request).read()
    except:
        # if urllib.error.HTTPError == 404:  <- werk niet nog uitzoeken
        print('Error: API does not return anything (key error, no access rights?)')
        print('URL: '+thisUrl)
        return ''

    json_data = json.loads(page)

    while(len(page) > 10):
        pageNr += 1
        thisUrl = url+str(pageNr)

        request = urllib.request.Request(thisUrl)
        request.add_header("Authorization", "Bearer "+api_key)

        page = urllib.request.urlopen(request).read()
        json_data += (json.loads(page))

    return(json_data)


def slashJoin(*args):
    return "/".join(arg.strip("/") for arg in args)


def updateUsers(courseId):
    apiPath='users'
    tableName='user'
    fields=['id','name','login_id']

    json_data = getJsonData(apiPath, courseId)

    sql = ""
    cnt = 0
    for i, item in enumerate(json_data):
        fieldStrings = ''
        fieldValues = ''
        for thisField in fields:
            fieldStrings += thisField+','
            fieldValues += '\'' + \
                str(item.get(thisField, courseId))+'\','
            if ( thisField == 'id' ):
                sql += "DELETE FROM " + tableName + " WHERE id = " + str(item.get(thisField, courseId)) + ";\n"
        fieldStrings = fieldStrings[:-1]
        fieldValues = fieldValues[:-1]
        student_nr = str(item.get(thisField, 'login_id')).split("@")[0]
        if (student_nr.isnumeric()):
            sql += "INSERT INTO " + tableName + \
                " (" + fieldStrings + ",student_nr) VALUES (" + fieldValues + ", '" + student_nr + "');\n"
            cnt += 1
    
    print(sql)
    print()
    print("Total users in this course: "+str(cnt))
    return()

#### MAIN ####

print("Database: "+dbName)

# connect to MySQL
try:
    con = pymysql.connect(host='localhost', user=dbUser, passwd=dbPassword, db=dbName, client_flag=CLIENT.MULTI_STATEMENTS)
    cursor = con.cursor()
except:
    dd('Error: cannot connect to database '+dbName)


# update users
updateUsers(7720)
exit(0)
