from pymysql.constants import CLIENT
import pymysql
import configparser

import win32com.client as win32

def Emailer(text, subject, recipient):
    outlook = win32.Dispatch('outlook.application')
    mail = outlook.CreateItem(0)
    mail.To = recipient
    mail.Subject = subject
    mail.HtmlBody = text
    mail.send

config = configparser.ConfigParser()
config.read("canvas.ini")

dbName = config.get('database', 'db')
dbUser = config.get('database', 'user')
dbPassword = config.get('database', 'password')

print("Database: %s" % dbName)

con = pymysql.connect(host='localhost', user=dbUser, passwd=dbPassword, db=dbName, client_flag=CLIENT.MULTI_STATEMENTS)
cursor = con.cursor()

cursor.execute("select name, login_id, code  from user")

# Fetch rows
imtems = cursor.fetchall()

for item in imtems:
    voornaam = item.name.split(' ')[0]
    code = item.code
    mail_address = item.login_id

    mail_body = f"Beste {voornaam},<br>Jouw personal Canvas Monitor link is: https://c20.cmon.ovh/public?code={code}"
    mail_subject = "Personal Link for Canvas Monitor"
    
    print("Emailer(mail_body, mail_subject, mail_address)")




