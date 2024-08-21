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
from threading import Thread
from datetime import datetime, timedelta

# DST_DATES={'2022':[27,30],'2023':[26,29],'2024':[31,27],'2025':[30,26],'2026':[29,25]}
DST_DATES = {
    '2022': [27, 30],
    '2023': [26, 29],
    '2024': [31, 27],
    '2025': [30, 26],
    '2026': [29, 25],
    '2027': [28, 31],
    '2028': [26, 29],
    '2029': [25, 28],
    '2030': [31, 27]
}

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

parser = argparse.ArgumentParser(description='Update resultaten in Canvas Monitor')
parser.add_argument('-c', '--course', type=int, help='Update course id or update all courses with a specified prio smaller than specified.', required=False)
parser.add_argument('-a', '--assignment', type=int, help='Update assignment group id', required=False)
parser.add_argument('-d', '--dry-run', action='store_true', help='Dry run for -a option (does all but execute updates/inserts in Canvas db', required=False)
parser.add_argument('--database', type=str, help='Database name, if ommited, the name from the ini file will be used', default='database', required=False)
parser.add_argument('--delete', action='store_true', help='Delete only - clear all data for this course', required=False)
parser.add_argument('-l', '--log', help='Loglevel 0: no logging, 1:progress log, 2:advanced log 3:even more', default=2, required=False)
args = vars(parser.parse_args())

try:
    databaseSectionName=args['database']
    dbName = config.get(databaseSectionName, 'db')
    dbUser = config.get(databaseSectionName, 'user')
    dbPassword = config.get(databaseSectionName, 'password')
except:
    dd('Error reading canvas.ini database parameters')


logLevel        = args['log']
prio            = args['course']
assignmentGroup = args['assignment']
dryRun          = args['dry_run']

if (prio is None and assignmentGroup is None):
    print()
    print(sys.argv[0]+' -c <curus_id> or -a <assignment_group> is required')
    print()
    print('  -c 0 does only a recalc of passed/notpassed criterea')
    print('  -c <low_numer> updates all courses with prio samller or equal than <low_number>')
    print()
    print('  -a <unknown assignment group> show all groups and courses available (from Canvas Database)')
    print('  -a -1 update all assignment groups that are resently changed')
    print()
    print('  -h help')
    sys.exit()

if ( prio == "" and assignmentGroup == "" ):
    print('-c course (offline) or -a assignemnt (online) need to be specified!')
    sys.exit()

if ( args['delete'] and int(args['course'])<100 ):
    print('Can only delete if course id is specified',0)
    sys.exit()

#### FUNCTIONS ####

def getCourses(prio=3):
    courses = {}
    sql = "select id, naam from course where update_prio<="+str(prio)
    cursor.execute(sql)
    for row in cursor:
        courses[row[0]] = row[1]
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
        print("Warning: No voldaan criteria read from database (table: module_def empty?")
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
        print('Error: API does not return anything (key error, no access rights?)')
        print('URL: '+thisUrl)
        return 'error'

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

def convertDate(stringDate): # converts GMT to CET and correct for DST
    dstOffset=1
    datetimeObject = datetime.strptime(stringDate, '%Y-%m-%dT%H:%M:%SZ')
    thisYear=datetimeObject.year
    thisMonth=datetimeObject.month
    thisDay=datetimeObject.day

    if ( str(thisYear) not in DST_DATES ):
       log(' *** Year for DST not defined, take defautl offset (+1) ***', 1)
    elif ( thisMonth>3 and thisMonth<10):
        dstOffset=2
    elif (thisMonth==3 and thisDay>=DST_DATES[str(thisYear)][0]):
        dstOffset=2
    elif (thisMonth==10 and thisDay<=DST_DATES[str(thisYear)][1]):
        dstOffset=2
    
    datetimeObject = datetime.strptime(stringDate, '%Y-%m-%dT%H:%M:%SZ') + timedelta(hours=dstOffset)
    return datetimeObject.strftime('%Y-%m-%d %H:%M:%S')


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
        elif(len(val) == 20 and val[10] == 'T' and val[19] == 'Z'): # e.g. 2023-01-09T08:18:54Z -> 2023-01-09 18:18:54
            return convertDate(val)
        
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

    if json_data=='error':
        log('API error, gracefully stopping import for table '+tableName,0)
        sys.exit(1)

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

    return(returnList) # return list of id's f.e. ['8131', '8127', '8128', '8324']

# Clear course (if requested from command line param)
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

    log("Create aggregate into resultaat", 1)
    # Note Grader id < 0 are automatically graded assignments; where s.grader_id > 0
    sql = """
        INSERT into resultaat (course_id, module_id, module, module_pos, student_nummer, klas, student_naam, ingeleverd, ingeleverd_eo, punten, minpunten, punten_max, punten_eo, laatste_activiteit,laatste_beoordeling, aantal_opdrachten)
        SELECT
            a.course_id AS course_id,
            g.id AS module_id,
            case when d.naam is null then g.name else d.naam end AS module,
            case when d.pos is null then 999 else d.pos end AS module_pos,
            u.student_nr AS student_nummer,
            u.klas AS klas,
            u.name AS student_naam,
            SUM(case when s.workflow_state<>'unsubmitted' then 1 else 0 end) AS ingeleverd,
            SUM(case when s.workflow_state<>'unsubmitted' and a.name like '%eind%' then 1 else 0 end) AS ingeleverd_eo,
            SUM(IFNULL(s.entered_score, 0)) AS punten,
            MIN(IFNULL(s.entered_score, 0)) AS minpunten,
            SUM(IFNULL(a.points_possible, 0)) AS punten_max,
            SUM(case when a.name like '%eind%' then s.entered_score else 0 end) AS punten_eo,
            MAX(submitted_at),
            MAX(case when s.grader_id>0 then graded_at else "1970-01-01 00:00:00" end),
            SUM(1) aantal_opdrachten
        FROM user u, assignment a
            left join submission s on s.assignment_id= a.id
            left join assignment_group g on g.id = a.assignment_group_id 
            left join module_def d on d.id=g.id
        WHERE length(u.klas) > 0
        AND (a.published = 1 OR a.published IS NULL)
        group by 1, 2, 3, 4, 5, 6, 7;
    """
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
            select (SUM(case when voldaan='V' then 1 else 0 end))*100+round(100*sum(punten/punten_max)) 'Ranking Score'
            FROM resultaat r
            inner join module_def d on d.id=r.module_id 
            where u.student_nr=r.student_nummer
            and d.generiek=0
            and punten_max > 0
        )"""
    cursor.execute(sql)
    con.commit()
    # normuren achieved are registered per module per student. This figure is stored n times per assignement
    # this should be normilized but there is not yet an results table per assignment

    # first calculate the bits and pieces normuren per not yet finished module
    sql="""
        UPDATE resultaat t1
        INNER JOIN ( SELECT r.student_nummer, module_id, round(sum( round(d.norm_uren*(r.punten*10/r.punten_max))  )/10) sum_norm_uren
            FROM resultaat r
            INNER JOIN module_def d ON d.id=module_id AND d.generiek=0
            WHERE r.voldaan!='V'
            and punten_max > 0
            GROUP BY r.student_nummer, module_id ) t2 ON t2.student_nummer=t1.student_nummer and t2.module_id=t1.module_id
        SET norm_uren = sum_norm_uren;
    """
    # ...then add the finished modules
    sql+="""
        UPDATE resultaat t1
        INNER JOIN ( SELECT r.student_nummer, module_id, sum( d.norm_uren) sum_norm_uren
            FROM resultaat r
            INNER JOIN module_def d ON d.id=module_id AND d.generiek=0
            WHERE r.voldaan='V'
            GROUP BY r.student_nummer, module_id ) t2 ON t2.student_nummer=t1.student_nummer and t2.module_id=t1.module_id
        SET t1.norm_uren = sum_norm_uren;
    """
    cursor.execute(sql)
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


#### Fast multi processing INSERT/UPDATE ####

# Helper function to show assignemntgroup info
# Input:  assignment group id
# Output: cursus_id, assignment_group_id, assignment group name (when no id is given show all)
def showAGroups(id=''):
    sql="SELECT g.course_id, g.id, g.name, m.naam FROM assignment_group g join module_def m on m.id=g.id"
    if (id):
        sql+=' where g.id='+str(id)
    sql+=" order by m.pos"

    cursor.execute(sql)
    assignments = cursor.fetchall()
    log("\n%32s %32s %10s %10s" % ( 'Canvas Name', 'Canvas Monitor Name', 'A.Group', 'Course', ) ,1 )
    prev=''
    for item in assignments:
        if (prev != item[0]) :
            log('',1)
            prev=item[0]
        log("%32s %32s %10s %10s" % (item[2], item[3], item[1], item[0]) , 1 )
    log('',1)


# Function to check if teh Canvas Database assignemnt-group data is in sync with the actual Canvas status
# We do this by comparing all assignment-id's of the assignment_group
# Input:  assignmnentGroup id, list of lists (output from sql-query). Element[1] contians assignemnt id
# Output: a list of differnt id's. Empty (flase) list means no differences
def checkAssignmentGroups(assignementGroup, assignments):
    dbList=[]
    for dbAssignment in assignments:
        dbList.append(dbAssignment[1])

    canvasAssignments=getJsonData('assignment_groups/'+str(assignementGroup)+'/assignments', str(assignments[0][0])) # returns list of dicts 
    canvasList=[]
    for canvasAssignment in canvasAssignments:
        canvasList.append(canvasAssignment['id'])

    return list(set(dbList)-set(canvasList))

# wrapper for updateAssignmentGroup, this fucntion updates all (recent-hot) assignment groups.
# recent-hot is defeined in the SQL query
def updateAssignmentGroups():
    # sql="select distinct assignment_group_id from submission s join assignment a on a.id=s.assignment_id where datediff(curdate(),submitted_at) < 7 or datediff(curdate(),graded_at) < 7"
    sql="SELECT distinct g.id, g.course_id, g.name, m.naam FROM assignment_group g JOIN module_def m on m.id=g.id JOIN assignment a on a.assignment_group_id=g.id JOIN submission s on s.assignment_id=a.id where datediff(curdate(),submitted_at) < 7 or datediff(curdate(),graded_at) < 7 order by m.pos"

    cursor.execute(sql)
    assignementGroups = cursor.fetchall()
    for assignementGroup in assignementGroups:
        updateAssignmentGroup(assignementGroup[0])


# Main entry point for fast updating all assignments belonging to the assignmnet group
# Input:  assignmnent_group_id
# Ouptut: none
def updateAssignmentGroup(assignementGroup):
    # fieldList, these fields are selected from the api and will be inserted/updated to the table submission with the same column names
    fieldList=['id', 'assignment_id', 'user_id', 'grader_id', 'preview_url', 'submitted_at', 'attempt', 'graded_at', 'excused', 'entered_score', 'workflow_state']

    # TODO, error, this could be wrong the database can contain old data, assignments in assignmentgrousp can be changed
    sql="select course_id, id, name from assignment where assignment_group_id="+str(assignementGroup)

    cursor.execute(sql)
    assignments = cursor.fetchall()

    if ( not assignments ):
        print('Invalid assignemntGroup '+str(assignementGroup) )
        showAGroups()
        sys.exit()
    else:
        showAGroups(assignementGroup)
    
    # Check if assignment_group->assignments are same in Canvas and in Canvas DB

    thisDryRun=dryRun
    if ( checkAssignmentGroups(assignementGroup, assignments) ):
        print()
        print('      *** Forced dry run *** ')
        print('Assignment(group) in Canvas DB is not in sync with Canvas.')
        print('Run full update on course with: '+sys.argv[0]+' -c '+str(assignments[0][0]))
        print('      *** Forced dry run *** ')
        print()
        thisDryRun=True
        # return

    # Canvas-Monitor submissions, dbSubmissions is dict[submission_id] of lists[fieldList]
    dbSubmissions={}
    sql="select s."+ ",s.".join(fieldList) +" from submission s join assignment a on a.id=s.assignment_id where a.assignment_group_id="+str(assignementGroup)
    cursor.execute(sql)
    submissions = cursor.fetchall()
    for item in submissions:
        thisList=[] 
        for i in range(0, len(fieldList)):
            thisList.append ( validate_string(fieldList[i],str(item[i])) )
        dbSubmissions[str(item[0])] = thisList

    sql=''
    threadNumber=0
    threads = []
    results = [{} for x in assignments]

    for courseId, assignmentID, asName in assignments:

        # this part needs to be multi threaded
        #   (non-threaded): sql+=executeThread(fieldList, courseId, assignmentID, asName, dbSubmissions)
        process = Thread(target=executeThread, args=[fieldList, courseId, assignmentID, asName, dbSubmissions, results, threadNumber])
        threadNumber+=1
        process.start()
        threads.append(process)

    for process in threads:
        process.join()

    sql = ''.join(map(str, results))

    log(sql, 3)
    log(' *** Total Number of inserts %3d, updates %3d *** '%(sql.count('insert'), sql.count('update')), 1)
    log('',1)
    log('Inserted %d and updated %d assignments'%(sql.count('insert'), sql.count('update')), 0)
   
    if (sql):
        if (thisDryRun):
            log('Dry run, no updates will be applied to Canvas Database',1)
        else:
            cursor.execute(sql)
    else:
        log('No updates',1)

    cursor.execute("update module_def set last_updated=now() where id="+str(assignementGroup))
    con.commit()



# wrapper for threads
# Input:
# Output: sql statements written in results[threadNumber]
def executeThread(fieldList, courseId, assignmentID, asName, dbSubmissions, results, threadNumber):
    log('Started thread '+str(threadNumber)+' for assignment '+str(assignmentID)+" "+asName, 2)
    canvasSubmissions=getCanvasSubmissions(fieldList, courseId, assignmentID)
    results[threadNumber]=getUpdates(fieldList, courseId, canvasSubmissions, dbSubmissions, asName, threadNumber)

# This returns a dict[submission_id] of lists from the Canvas API
def getCanvasSubmissions(fieldList, courseId, assignmentID):
    canvasSubmissions={}
    submissions=getJsonData('assignments/'+str(assignmentID)+'/submissions', str(courseId)) # returns list of dicts
    for item in submissions:
        thisList=[]
        for key in fieldList:
            thisList.append ( validate_string(key,item[key]) )
        canvasSubmissions[str(item['id'])] = thisList
    return canvasSubmissions

# Check we have canvasSubmisions and dbSubmissions. Copare these and create sql for the inserts and updates 
# Input:
# Output: sql containing updates and inserts
def getUpdates(fieldList, courseId, canvasSubmissions, dbSubmissions, asName, threadNumber):
    sql=''
    inserts=0
    updates=0
    for key in canvasSubmissions:
        if key in dbSubmissions: 
            if ( canvasSubmissions[key][5] == dbSubmissions[key][5] and canvasSubmissions[key][7] == dbSubmissions[key][7] ):
                continue # key found but submitted_at and graded_at are not changed. No update needed
            #start UPDATE
            fields=""
            for i in range(1, len(fieldList)):
                fields = fields +fieldList[i]+"=\'"+canvasSubmissions[key][i]+"\',"
            sql+="update submission set "+fields[:-1]+" where id="+key+";\n"
            updates+=1
        else:
            # submission does not exists at all, start INSERT
            # first field is course_id which is not privided from the json (api)
            fields="course_id,"
            values=str(courseId)+","
            for i in range(0, len(fieldList)):
                fields = fields + fieldList[i] + ","
                values = values +"\'"+ canvasSubmissions[key][i] + "\',"
            sql+="insert into submission ( "+fields[:-1]+" ) values ("+values[:-1]+");\n"
            inserts+=1

    log('Thread %2d: number of inserts %3d, updates %3d for %s'%(threadNumber, inserts, updates, asName), 2)
    return sql




#### MAIN ####

log("Database: "+dbName, 1)
log("Loglevel: "+str(logLevel), 1)

# connect to MySQL
try:
    con = pymysql.connect(host='localhost', user=dbUser, passwd=dbPassword, db=dbName, client_flag=CLIENT.MULTI_STATEMENTS)
    cursor = con.cursor()
except:
    dd('Error: cannot connect to database '+dbName)

# Read voldaan_criteria['module_id']='punten > 12' read from table module_def
voldaan_criteria=getVoldaanCriteria()


if (assignmentGroup):
    if (assignmentGroup==-1):
        log('Doing all assignment groups, updated in last 7 days ....', 1)
        updateAssignmentGroups()
    else:
        updateAssignmentGroup(assignmentGroup)
    sys.exit()

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
        for course_id, course_name in courses.items():
            if (course_id != 0 ):
                log("Create "+str(course_name)+" ("+str(course_id)+")",1)
                createBlok(course_id)

createResultaat()
calcVoldaan()
calcRanking()
log("Recalc Voldaan Done",1)


con.close()
