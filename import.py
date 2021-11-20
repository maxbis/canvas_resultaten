import pymysql, os, json, csv
import urllib.request

baseUrl  = 'https://talnet.instructure.com/api/v1/courses/'
courseId = '2101'
paramUrl = '?per_page=100&page='
api_key  = "17601~LZ3pktnGAYnvWPXvIsqjGNY1bg1LfSH1fOfVvmoCAG9AmKX3mDZIyzPBsmnO1iZw"

def getJsonData(url, courseId):
    pageNr=1
    json_obj={}

    url = slashJoin(baseUrl, str(courseId), url, paramUrl)
    thisUrl=url+str(pageNr)

    print(thisUrl)

    request = urllib.request.Request(thisUrl)
    request.add_header("Authorization", "Bearer "+api_key)

    try:
        page=urllib.request.urlopen(request).read()
    except:
        #if urllib.error.HTTPError == 404:  <- werk niet nog uitzoeken
        return(json_obj)

    
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
    #json_data=getJsonData("modules/")
    #fields=[ 'id', 'name', 'position']
    #tableName='modules'

    json_data=getJsonData(apiPath, courseId)   

    # parse json data to SQL insert

    if doDelete:
        sql="delete from "+tableName+" where course_id="+str(courseId)
        print(sql)
        cursor.execute(sql)
    count=1
    returnList=[]
    for i, item in enumerate(json_data):
        fieldStrings=''
        fieldValues=''
        first=True # first fieldname in list of fields; this field usually id is returned as a list
        for thisField in fields:
            if (first):
                returnList.append(validate_string(thisField, item.get(thisField, None)))
                first=False
            fieldStrings += thisField+','
            fieldValues += '\''+validate_string(thisField, item.get(thisField, courseId))+'\',' # if fieldname is not found in dict use course_id
        fieldStrings = fieldStrings[:-1]
        fieldValues = fieldValues[:-1]
        sql="INSERT INTO " + tableName + " (" + fieldStrings + ") VALUES (" + fieldValues + ")"

        print(count, sql)
        cursor.execute(sql)
        count+=1
        
    con.commit()
    return(returnList)

def createBlok(course_id):
    importTable(course_id,"assignment_groups", "assignment_group", ['id','name','course_id'])
    importTable(course_id,"modules", "module", [ 'id', 'name', 'position', 'items_count',  'published', 'course_id'])
    returnList=importTable(course_id,"assignments", "assignment", [ 'id', 'points_possible', 'assignment_group_id', 'name', 'course_id'])
    deleteAll=True
    for item in returnList: # itterate through all assignments and retrieve all submissions for each assignement (due to limitation set by Canvas admin this cannot be done in one go) 
        print("Item: "+item)
        inserts=importTable(course_id,"assignments/"+item+"/submissions", "submission", [ 'id', 'assignment_id', 'user_id', 'grader_id', 'preview_url', 'submitted_at', 'graded_at', 'excused', 'entered_score', 'workflow_state','course_id'], deleteAll)
        print("return: "+str(len(inserts)))
        deleteAll=False # since we itterate through all submissions in this course, only clean up entire table in the first itteration

def createResultaat():
    # put all results as displayed in GUI in one table (for performance reason)
    sql="delete from resultaat"
    print(sql)
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
    group by 1, 2, 3, 4, 5
    """
    print("Create resultaat")
    cursor.execute(sql)
    con.commit()

def calcVoldaan():
    voldaan_criteria={
        'Opdrachten Introductie':'ingeleverd_eo=1',
        'Opdrachten basic IT' :'ingeleverd>10',
        'Opdrachten Front End Level 1':'punten>=90',
        'Opdrachten Challenge':'punten_eo>30',
        'CMS - Level 1':'punten > 30',
        'Think Code - Level 1':'punten_eo> 2',
        'Front End - Level 2':'punten_eo> 30',
        'Opdrachten DevOps':'punten_eo>=15',
    } 
 
    sql="update resultaat set voldaan='-'" # reset all V to - (nothing is 'voldaan')
    cursor.execute(sql)

    for item in voldaan_criteria: #check all voldaan criterea and put V when criteria is met
        sql="update resultaat set voldaan = 'V' WHERE module = '"+ item +"' and "+ voldaan_criteria[item]
        print("Update resultaat: "+sql)
        print("Update resultaat")
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


# connect to MySQL
con = pymysql.connect(host = 'localhost',user = 'root',passwd = '',db = 'canvas')
cursor = con.cursor()


# Never use this anymore since Users table is enriched via queries
#importTable(2101,"users", "user", ['id', 'name', 'login_id'])


# Blok1
if (1):
    createBlok(2101)

# Blok 2
if (1):
    createBlok(2110)

# calc resultaat
if(1):
    createResultaat()
    calcVoldaan()
    createCsv()

con.close()