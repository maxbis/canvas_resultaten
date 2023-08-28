from pymysql.constants import CLIENT
import pymysql
import configparser

import win32com.client as win32

import sys

def Emailer(text, subject, recipient):
    outlook = win32.Dispatch('outlook.application')
    mail = outlook.CreateItem(0)
    print(f"email to: {recipient}")
    mail.To = recipient
    mail.Subject = subject
    mail.HtmlBody = text
    mail.send
    print("email sent")

config = configparser.ConfigParser()
config.read("canvas.ini")

dbName = config.get('canvas-c23', 'db')
dbUser = config.get('canvas-c23', 'user')
dbPassword = config.get('canvas-c23', 'password')

print("Database: %s" % dbName)

con = pymysql.connect(host='localhost', user=dbUser, passwd=dbPassword, db=dbName, client_flag=CLIENT.MULTI_STATEMENTS)
cursor = con.cursor(pymysql.cursors.DictCursor)

cursor.execute("select name, login_id, code from user")

# Fetch rows
items = cursor.fetchall()


for item in items:
    code = item['code']
    if code != None and len(code) > 10:
        voornaam = item['name'].split(' ')[0]
        mail_address = item['login_id']

        mail_body = f"Beste {voornaam},<br>Jouw personal Canvas Monitor link is: https://c23.cmon.ovh/public?code={code}"
        mail_subject = "Personal Link for Canvas Monitor"
        
        print(f"Emailer('{mail_body}', '{mail_subject}', '{mail_address}')")
        Emailer(mail_body, mail_subject, mail_address)
        




