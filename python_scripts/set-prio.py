from pymysql.constants import CLIENT
import pymysql
import configparser

config = configparser.ConfigParser()
config.read("canvas.ini")

dbName = config.get('database', 'db')
dbUser = config.get('database', 'user')
dbPassword = config.get('database', 'password')

print("Database: %s" % dbName)

con = pymysql.connect(host='localhost', user=dbUser, passwd=dbPassword, db=dbName, client_flag=CLIENT.MULTI_STATEMENTS)
cursor = con.cursor()

cursor.execute("select course_id, count(*) from submission s where datediff(curdate(),s.submitted_at) < 8 group by 1 order by 2 desc")

# Fetch rows
data = cursor.fetchall()

print("Info : %s " % str(data) )

total=0
for item in data:
    total+=item[1]

totalPercentage=0
for item in data:
    thisPercentage=item[1]*100/total
    totalPercentage+=thisPercentage
    if (totalPercentage<80 and item[1]>9):
        prio=1
    elif(thisPercentage >10 and item[1]>9) :
        prio=2
    else:
        prio=3
    sql="update course set update_prio="+str(prio)+" where id="+str(item[0])
    print("Aantal: %3d prio: %d" % ( item[1],prio ))
    print(" Query: %s" % sql)
    # cursor.execute(sql)