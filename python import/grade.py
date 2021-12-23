from canvasapi import Canvas
import configparser

config = configparser.ConfigParser()
config.read("canvas.ini")

# baseUrl = config.get('main', 'baseUrl')
# paramUrl = config.get('main', 'paramUrl')
# api_key = config.get('main', 'api_key')
# dbName = config.get('database', 'db')
# dbUser = config.get('database', 'user')
# dbPassword = config.get('database', 'password')

#https://canvasapi.readthedocs.io/en/stable/examples.html

# Canvas API URL
API_URL = config.get('main', 'host')
# Canvas API key
API_KEY = config.get('main', 'api_key')

# Initialize a new Canvas object
canvas = Canvas(API_URL, API_KEY)

course = canvas.get_course(2101)
print(course.name)

assignment = course.get_assignment(17855)
# 17851 Google beter leren kennen :  if ( att['size']> 50000 and att['content-type']=='application/pdf' ): 10
# 17843 Basiskennis netwerkstructuren : if ( 'computer' in body or 'pc' in body ): 5
# 17847 PDF : 10
# 17855 PDF : if ( att['size']> 50000 and att['content-type']=='image/jpeg' ): 10
# 

print(assignment)

submissions = assignment.get_submissions()

#submission = assignment.get_submission(1386)
for submission in submissions:
    thisPass=False
    print(submission.user_id)
    body = submission.body
    if (body):   
        print(body)
        if ( 'computer' in body or 'pc' in body ):
            thisPass=False
    if (hasattr(submission,'attachments')):
        for att in submission.attachments:
            print(att['display_name'], att['content-type'], att['size'])
            if ( att['size']> 50000 and att['content-type']=='image/jpeg' ):
                thisPass=True

    if (thisPass):
        print(' *** Passed *** ')
        submission.edit(submission={'posted_grade':10})

    print('\n------------------\n')

