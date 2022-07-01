#! /usr/bin/python3

from pymysql.constants import CLIENT
import pymysql
import os
import json
import csv
import urllib.request
import argparse

import configparser

config = configparser.ConfigParser()
config.read("canvas.ini")

baseUrl = config.get('main', 'baseUrl')
paramUrl = config.get('main', 'paramUrl')
api_key = config.get('main', 'api_key')
dbName = config.get('database', 'db')
dbUser = config.get('database', 'user')
dbPassword = config.get('database', 'password')

parser = argparse.ArgumentParser(description='Update resultaten in Canvas Monitor')
parser.add_argument('-c', '--course', type=int, help='Update course id or update all courses with a specified prio smaller than specified.', required=True)
parser.add_argument('--delete', action='store_true', help='Delete only - clear all data for this course', required=False)
parser.add_argument('-l', '--log', help='Loglevel 0: no logging, 1:progress log, 2:advanced log', default=2, required=False)
args = vars(parser.parse_args())

logLevel = args['log']
prio     = args['course']

print("Database: "+dbName)
print("Loglevel: "+str(logLevel))

if ( args['delete'] and int(args['course'])<100 ):
    log('Can only delete if course id is specified',0)

#### FUNCTIONS ####

def getCourses(prio=3):
    courses = []
    sql = "select id from course where update_prio<="+str(prio)
    cursor.execute(sql)
    for row in cursor:
        courses.append(row[0])
        log('Course found: '+str(row[0]),3)
    return(courses)

# Read voldaanCriteria (these are where clauses needed to determnine if module is 'Voldaan')
def getVoldaanCriteria():
    voldaan_criteria = {}
    sql = "select id, voldaan_rule from module_def"
    cursor.execute(sql)
    for row in cursor:
        voldaan_criteria[row[0]] = row[1]

    if(len(voldaan_criteria) < 1):
        print("Error: No voldaan criteria read from database")
        exit(1)
    return(voldaan_criteria)

def log(message, level=3):
    if level <= int(logLevel):
        print(message)

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
        print('Error: API does not return anything (key error, geen rechten?)')
        print('URL: '+thisUrl)
        exit(1)
        # return(json_obj)

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

# do validation and checks before insert

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
            return val[:-1]
        else:
            # remove ' from strings and return string, ToDo make a proper fully escaped string
            return val.replace('\'', '')
    else:  # value is None (NULL)
        # hack to translate null date into actual date (constraint MariaDB)
        if(fieldName in ['submitted_at',  'graded_at']):
            return "1970-01-01 00:00:00"
        else:
            return '0'

def importTable(courseId, apiPath, tableName, fields, doDelete=True):
    json_data = getJsonData(apiPath, courseId)

    # ToDo check if data is returned else exit(1)

    # parse json data to SQL insert

    if doDelete:
        sql = "delete from "+tableName+" where course_id="+str(courseId)
        log(sql, 3)
        cursor.execute(sql)
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
                validate_string(thisField, item.get(thisField, courseId))+'\','
        fieldStrings = fieldStrings[:-1]
        fieldValues = fieldValues[:-1]
        sql += "INSERT INTO " + tableName + \
            " (" + fieldStrings + ") VALUES (" + fieldValues + ");\n"

        log(count, 3)
        count += 1

    if (sql):
        log('Execute '+str(count)+' insert SQL statements in '+str(tableName)+' for course '+str(courseId), 1)
        log("------------------\n"+sql+"------------------\n", 3)
        cursor.execute(sql)
        con.commit()

    return(returnList)

# Clear course
# delete from assignment_group where course_id = xxx;
# delete from module where course_id = xxx;
# delete from assignment where course_id=xxx
def deleteBlok(course_id):
    course_id=str(course_id)
    sql="delete from assignment_group where course_id = "+course_id+";"
    sql+="delete from module where course_id = "+course_id+";"
    sql+="delete from assignment where course_id = "+course_id+";"

    log('DELETE all data from course '+course_id, 2)
    log('SQL:\n'+sql, 3)

    cursor.execute(sql)
    con.commit()

def createBlok(course_id):
    importTable(course_id, "assignment_groups",
                "assignment_group", ['id', 'name', 'course_id'])
    importTable(course_id, "modules", "module", [
                'id', 'name', 'position', 'items_count',  'published', 'course_id'])
    returnList = importTable(course_id, "assignments", "assignment", [
                             'id', 'points_possible', 'assignment_group_id', 'name', 'course_id','position','published'])
    deleteAll = True
    # itterate through all assignments and retrieve all submissions for each assignement (due to limitation set by Canvas admin this cannot be done in one go)
    for item in returnList:
        log("Item: "+item, 3)
        inserts = importTable(course_id, "assignments/"+item+"/submissions", "submission", [
                              'id', 'assignment_id', 'user_id', 'grader_id', 'preview_url', 'submitted_at', 'attempt', 'graded_at', 'excused', 'entered_score', 'workflow_state', 'course_id'], deleteAll)
        log("return: "+str(len(inserts)), 3)
        deleteAll = False  # since we itterate through all submissions in this course, only clean up entire table in the first itteration

def createResultaat():
    # put all results as displayed in GUI in one table (for performance reason)
    sql = "delete from resultaat"
    log(sql, 3)
    cursor.execute(sql)
    con.commit()

    # Note Grader id < 0 are automatically graded assignments; where s.grader_id > 0
    sql = """
        INSERT into resultaat (course_id, module_id, module, module_pos, student_nummer, klas, student_naam, ingeleverd, ingeleverd_eo, punten, minpunten, punten_max, punten_eo, laatste_activiteit,laatste_beoordeling, aantal_opdrachten)
        SELECT
            a.course_id course_id,
            g.id module_id,
            case when d.naam is null then g.name else d.naam end module,
            case when d.pos is null then 999 else d.pos end module_pos,
            SUBSTRING_INDEX(u.login_id,'@',1) student_nummer,
            u.klas klas,
            u.name student_naam,
            SUM(case when s.workflow_state<>'unsubmitted' then 1 else 0 end) ingeleverd,
            SUM(case when s.workflow_state<>'unsubmitted' and a.name like '%eind%' then 1 else 0 end) ingeleverd_eo,
            sum(s.entered_score) punten,
            min(s.entered_score) minpunten,
            sum(a.points_possible) punten_max,
            sum(case when a.name like '%eind%' then s.entered_score else 0 end) punten_eo,
            max(submitted_at),
            max(case when s.grader_id>0 then graded_at else "1970-01-01 00:00:00" end),
            sum(1) aantal_opdrachten
        FROM assignment a
        join submission s on s.assignment_id= a.id join user u on u.id=s.user_id
        join assignment_group g on g.id = a.assignment_group_id 
        left outer join module_def d on d.id=g.id
        WHERE u.klas is not null
        and published=1
        group by 1, 2, 3, 4, 5, 6, 7
    """
    log("Create aggregate into resultaat", 1)
    cursor.execute(sql)
    con.commit()

def calcVoldaan():
    # reset all V to - (nothing is 'voldaan')
    sql = "Update resultaat set voldaan='-'"
    cursor.execute(sql)

    log("Create resultaat set voldaan", 1)

    for item in voldaan_criteria:  # check all voldaan criterea and put V when criteria is met
        # sql="update resultaat set voldaan = 'V' WHERE module = '"+ item +"' and "+ voldaan_criteria[item]
        sql = "update resultaat set voldaan = 'V' WHERE module_id = " + \
            str(item) + " and " + voldaan_criteria[item]
        log("Update resultaat: "+sql, 2)
        cursor.execute(sql)

    # log import done    
    sql="insert into log (subject, message, route) values('Import', (select concat(sum(1),'-',sum(ingeleverd))from resultaat), '');"
    cursor.execute(sql);
    con.commit()

def calcRanking():
    sql="""
        update user u set ranking_score=
        (
            select (SUM(case when voldaan='V' then 1 else 0 end))*100+round(100*sum(punten)/max(punten_max)) 'Ranking Score'
            FROM resultaat r
            inner join module_def d on d.id=r.module_id 
            where u.student_nr=r.student_nummer
        )"""
    cursor.execute(sql);
    con.commit()

def createCsv():
    sql = "select * from resultaat"
    print("Query CSV: "+sql)
    cursor.execute(sql)
    rows = cursor.fetchall()
    fp = open('./canvas_resultaat.csv', 'w')
    myFile = csv.writer(fp)
    myFile.writerows(rows)
    fp.close()

#### MAIN ####



# connect to MySQL
con = pymysql.connect(host='localhost', user=dbUser, passwd=dbPassword, db=dbName, client_flag=CLIENT.MULTI_STATEMENTS)
cursor = con.cursor()

# Read voldaan_criteria['module_id']='punten > 12' read from table module_def
voldaan_criteria=getVoldaanCriteria()


#if prio > 100 assume it is a course ID and only update that course (for debugging)
if (int(args['course'])>100):
    if ( args['delete'] ):
        deleteBlok(int(args['course']))
    else:
        print("Create one block: "+str(args['course']))
        createBlok(int(args['course']))
else:
    # Read course_id's that need to be updated from database table course
    if (int(args['course']) != 0):
        courses=getCourses( args['course'] )
        log("About to update "+str(len(courses))+" courses (blokken)", 1)
        for blok in courses:
            if (blok != 0 ):
                log("Create "+str(blok),1)
                createBlok(blok)

createResultaat()
calcVoldaan()
calcRanking()
log("Recalc Voldaan Done",1)


con.close()
