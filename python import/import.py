import pymysql, os, json, csv
import urllib.request
import argparse

import configparser

config = configparser.ConfigParser()
config.read("canvas.ini")

baseUrl    = config.get('main', 'baseUrl')
paramUrl   = config.get('main', 'paramUrl')
api_key    = config.get('main', 'api_key')
courses    = config.get('main', 'courses')
criteria   = config.get('voldaan_criteria', 'criteria')
dbName     = config.get('database', 'db')
dbUser     = config.get('database', 'user')
dbPassword = config.get('database', 'password')

# parse voldaan criteria from ini file into a dict (key/value array)
voldaan_criteria={}
for item in criteria.split('\n'):
    [key, value] = item.split(':')
    voldaan_criteria[key]=value

#check if criteria are read ok
# for item in voldaan_criteria:
#     sql="update resultaat set voldaan = 'V' WHERE module = '"+ item +"' and "+ voldaan_criteria[item]
#     print(sql)

# parse command line arguments
parser = argparse.ArgumentParser(description='Update resultaten in Canvas Monitor')
parser.add_argument('-c','--courses', nargs='+', type=int, help='update for course (ids) seperate list with spaces', required=True)
parser.add_argument('-v','--verbose', help='Loglevel 0:no logging, 1:progress log, 2:advanced log', default=0, required=False)
args = vars(parser.parse_args())

logLevel=args['verbose']


# connect to MySQL
from pymysql.constants import CLIENT
con = pymysql.connect(host='localhost',user=dbUser, passwd=dbPassword, db =dbName, client_flag=CLIENT.MULTI_STATEMENTS)
cursor = con.cursor()

def log(message, level=3):
    if level<=int(logLevel):
        print(message)

def getJsonData(url, courseId):
    pageNr=1
    json_obj={}

    url = slashJoin(baseUrl, str(courseId), url, paramUrl)
    thisUrl=url+str(pageNr)

    log(thisUrl,3)

    request = urllib.request.Request(thisUrl)
    request.add_header("Authorization", "Bearer "+api_key)

    try:
        page=urllib.request.urlopen(request).read()
    except:
        #if urllib.error.HTTPError == 404:  <- werk niet nog uitzoeken
        print('Error: API does not return anything (key error?)')
        # print('api_keu used: '+api_key)
        exit(1)
        # return(json_obj)

    json_data = json.loads(page)

    while( len(page) > 10 ):
        pageNr+=1
        thisUrl=url+str(pageNr)

        request = urllib.request.Request(thisUrl)
        request.add_header("Authorization", "Bearer "+api_key)

        page=urllib.request.urlopen(request).read()
        json_data+=(json.loads(page))

    return(json_data)

def slashJoin(*args):
    return "/".join(arg.strip("/") for arg in args)

# do validation and checks before insert
def validate_string(fieldName, val):
    if val != None:
            if type(val) is int or type(val) is float:
                #for x in val:
                #   print(x)
                return str(val)
            elif( type(val) is bool ):
                if (val):
                    return('1')
                else:
                    return('0')
            elif( len(val)==20 and val[10]=='T' and val[19]=='Z'): # string is like 2020-12-23T09:43:12Z ? remove trailing Z
                return val[:-1]
            else:
                return val.replace('\'','') # remove ' from strings and return string, ToDo make a proper fully escaped string
    else: # value is None (NULL)
        if(fieldName in ['submitted_at',  'graded_at']): # hack to translate null date into actual date (constraint MariaDB)
            return "1970-01-01 00:00:00"
        else:
            return '0'


def importTable(courseId, apiPath, tableName, fields, doDelete=True):
    json_data=getJsonData(apiPath, courseId)

    # parse json data to SQL insert

    if doDelete:
        sql="delete from "+tableName+" where course_id="+str(courseId)
        log(sql,3)
        cursor.execute(sql)
    count=1
    returnList=[]
    sql=""
    for i, item in enumerate(json_data):
        fieldStrings=''
        fieldValues=''
        first=True # first fieldname in list of fields; this field usually id is returned from this function as a list
        for thisField in fields:
            if (first):
                returnList.append(validate_string(thisField, item.get(thisField, None))) 
                first=False
            fieldStrings += thisField+','
            fieldValues += '\''+validate_string(thisField, item.get(thisField, courseId))+'\',' # if fieldname is not found in dict use course_id
        fieldStrings = fieldStrings[:-1] # remove last , (only last item can not have a , other fileds and values are seperated by , )
        fieldValues = fieldValues[:-1]   # remove last ,
        sql+="INSERT INTO " + tableName + " (" + fieldStrings + ") VALUES (" + fieldValues + ");\n"

        log(count,3)
        count+=1

    if (sql):
        log('Execute '+str(count)+' insert SQL statements in '+str(tableName)+' for course '+courseId,1)
        log("------------------\n"+sql+"------------------\n",3)
        cursor.execute(sql)
        con.commit()

    return(returnList) # return list of first field values (usually ids/keys)

def createBlok(course_id):
    importTable(course_id,"assignment_groups", "assignment_group", ['id','name','course_id'])
    importTable(course_id,"modules", "module", [ 'id', 'name', 'position', 'items_count',  'published', 'course_id'])
    returnList=importTable(course_id,"assignments", "assignment", [ 'id', 'points_possible', 'assignment_group_id', 'name', 'course_id'])
    deleteAll=True
    for item in returnList: # itterate through all assignments and retrieve all submissions for each assignement (due to limitation set by Canvas admin this cannot be done in one go)
        log("Item: "+item,3)
        inserts=importTable(course_id,"assignments/"+item+"/submissions", "submission", [ 'id', 'assignment_id', 'user_id', 'grader_id', 'preview_url', 'submitted_at', 'graded_at', 'excused', 'entered_score', 'workflow_state','course_id'], deleteAll)
        log("return: "+str(len(inserts)),3)
        deleteAll=False # since we itterate through all submissions in this course, only clean up entire table in the first itteration

def createResultaat():
    # put all results as displayed in GUI in one table (for performance reason)
    sql="delete from resultaat"
    log(sql,3)
    cursor.execute(sql)
    con.commit()

    sql="""
        insert into resultaat (course_id, module_id, module, student_nummer, klas, student_naam, ingeleverd, ingeleverd_eo, punten, punten_max, punten_eo, laatste_activiteit,laatste_beoordeling)
        SELECT a.course_id course_id,g.id module_id,g.name module, SUBSTRING_INDEX(u.login_id,'@',1) student_nummer, u.klas klas, u.name student_naam,
        SUM(case when s.workflow_state<>'unsubmitted' then 1 else 0 end) ingeleverd,
        SUM(case when s.workflow_state<>'unsubmitted' and a.name like '%eind%' then 1 else 0 end) ingeleverd_eo,
        sum(s.entered_score) punten,
        sum(a.points_possible) punten_max,
        sum(case when a.name like '%eind%' then s.entered_score else 0 end) punten_eo,
        max(submitted_at),
        max(graded_at)
        FROM assignment a
        join submission s on s.assignment_id= a.id join user u on u.id=s.user_id
        join assignment_group g on g.id = a.assignment_group_id
        group by 1, 2, 3, 4, 5, 6
    """
    log("Create aggregate into resultaat",1)
    cursor.execute(sql)
    con.commit()

def calcVoldaan():
    sql="Update resultaat set voldaan='-'" # reset all V to - (nothing is 'voldaan')
    cursor.execute(sql)

    log("Create resultaat set voldaan",1)

    for item in voldaan_criteria: #check all voldaan criterea and put V when criteria is met
        sql="update resultaat set voldaan = 'V' WHERE module = '"+ item +"' and "+ voldaan_criteria[item]
        log("Update resultaat: "+sql,2)
        cursor.execute(sql)
        con.commit()

def createCsv():
    sql="select * from resultaat"
    print("Query CSV: "+sql)
    cursor.execute(sql)
    rows = cursor.fetchall()
    fp = open('./canvas_resultaat.csv', 'w')
    myFile = csv.writer(fp)
    myFile.writerows(rows)
    fp.close()



blokken=[]
for item in args['courses']:
    try:
        blokken.append( config.get('courses', str(item)) )
    except:
        print(str(item)+" is not defined in config (under [courses])")
        exit()

count=0
for blok in blokken:
    print('Start course: '+blok,1)
    createBlok(blok)
    count+=1

if (count):
    createResultaat()
    calcVoldaan()


con.close()
