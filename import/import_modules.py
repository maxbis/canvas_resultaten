#! /usr/bin/python3

import pymysql
import os
import json
import csv
import urllib.request
import argparse

import configparser
import sys

from pprint import pprint
from pymysql.constants import CLIENT

# Debug-dump and die
def dd(arg):
    try:
        print(arg)
    except:
        pprint(arg)
    sys.exit()

parser = argparse.ArgumentParser(description='Update Canvas Modules in Canvas Monitor')
parser.add_argument('-c', '--course', type=int, help='Update course id', required=False)
parser.add_argument('--database', type=str, help='Database name, if ommited, the name from the ini file will be used', default='database', required=False)
args = vars(parser.parse_args())

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

try:
    databaseSectionName=args['database']
    dbName = config.get(databaseSectionName, 'db')
    dbUser = config.get(databaseSectionName, 'user')
    dbPassword = config.get(databaseSectionName, 'password')
except:
    dd('Error reading canvas.ini database parameters')




logLevel=1 # log all
def log(message, level=3):
    if level <= int(logLevel):
        print(message)

def slashJoin(*args):
    return "/".join(arg.strip("/") for arg in args)

def getJsonData(url, courseId):
    pageNr = 1
    json_obj = {}

    url = slashJoin(baseUrl, str(courseId), url, paramUrl)
    thisUrl = url+str(pageNr)

    log(thisUrl, 3)

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

def validate_string(fieldName, val):
    if val != None:
        if (type(val) is int or type(val) is float):
            return str(val)
        elif(type(val) is bool):
            if (val):
                return('1')
            else:
                return('0')
        # string is like 2020-12-23T09:43:12Z ? remove trailing Z
        elif(len(val) == 20 and val[10] == 'T' and val[19] == 'Z'):
            # val[10]=' ' # Does this work with the old import method (full overwrite)
            return val[:-1].replace('T', ' ')
        else:
            # remove ' from strings and return string, ToDo make a proper fully escaped string
            return val.replace('\'', '')
    else:  # value is None (NULL)
        # hack to translate null date into actual date (constraint MariaDB)
        if(fieldName in ['submitted_at',  'graded_at']):
            return "1970-01-01 00:00:00"
        else:
            return '0'

def importTable(courseId, apiPath, tableName, fields):
    json_data = getJsonData(apiPath, courseId)

    count = 1
    returnList = []
    sql = ""
    for i, item in enumerate(json_data):
        fieldStrings = ''
        fieldValues = ''
        first = True  # first fieldname in list of fields; this field usually id is returned as a list
        for thisField in fields:
            if (first):
                returnList.append(validate_string(
                    thisField, item.get(thisField, None)))
                first = False
            fieldStrings += thisField+','
            # if fieldname is not found in dict use course_id
            fieldValues += '\'' + \
                validate_string(thisField, item.get(thisField, ''))+'\','
        #fieldStrings = fieldStrings[:-1]
        #fieldValues = fieldValues[:-1]
        fieldStrings += 'course_id'
        fieldValues += str(courseId)

        sql += "INSERT INTO " + tableName + \
            " (" + fieldStrings + ") VALUES (" + fieldValues + ");\n"
        log(count, 3)
        count += 1
    
    if (sql):
        log('Execute '+str(count)+' insert SQL statements in '+str(tableName)+' for course '+str(courseId), 1)
        log("------------------\n"+sql+"------------------\n", 3)
        cursor.execute(sql)
        con.commit()
    
    return(returnList) # return list of id's f.e. ['8131', '8127', '8128', '8324']

def importModuleItems(course_id, module_id):
    sql = "delete from module_items where module_id="+str(module_id)
    log(sql, 3)
    cursor.execute(sql)
    importTable(course_id, "modules/"+str(module_id)+"/items", "module_items", [
                'id', 'title', 'position', 'type', 'html_url', 'content_id', 'published','module_id'])

def getModules(courseId):
    sql = "select id, name from module where course_id="+str(courseId)
    cursor.execute(sql)

    modules=[]
    for row in cursor:
         modules.append(row[0])

    for module in modules:
        module_id=module
        log('Processing module ' + str(module_id),1)
        importModuleItems(courseId, module_id)

def updateAllModules():
    sql = "select id from course"
    cursor.execute(sql)

    courses=[]
    for row in cursor:
         courses.append(row[0])

    for course in courses:
        getModules(course)

# connect to MySQL
try:
    con = pymysql.connect(host='localhost', user=dbUser, passwd=dbPassword, db=dbName, client_flag=CLIENT.MULTI_STATEMENTS)
    cursor = con.cursor()
except:
    dd('Error: cannot connect to database '+dbName)

#updateAllModules()
getModules(3237)
