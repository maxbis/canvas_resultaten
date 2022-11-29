from canvasapi import Canvas
import configparser
from pprint import pprint

config = configparser.ConfigParser()
config.read("canvas.ini")

def matchText(source, match, caseSens=False):
    count=0
    if ( caseSens == False):
        source=source.lower()
        match=match.lower()
    for word in source.split():
        if word in match:
            count+=1
    return count

wordFreq={}
def analyseText(source):
    source=source.lower()
    source=strip_tags(source)
    for word in source.split():
        # print('find word: '+word)
        if (len(word)>3):
            if ( word in wordFreq ):
                wordFreq[word]+=1
            else:
                wordFreq[word]=1

def scoreText(source, count):
    source=source.lower()
    source=strip_tags(source)
    score=0
    for word in source.split():
        if (len(word)>3):
            percentage=wordFreq[word]*100/count
            if (percentage>20):
                score=score+percentage
    return int(score)

from io import StringIO
from html.parser import HTMLParser

class MLStripper(HTMLParser):
    def __init__(self):
        super().__init__()
        self.reset()
        self.strict = False
        self.convert_charrefs= True
        self.text = StringIO()
    def handle_data(self, d):
        self.text.write(d)
    def get_data(self):
        return self.text.getvalue()

def strip_tags(html):
    s = MLStripper()
    s.feed(html)
    return s.get_data()

# print(matchText("echo [1][0]", "met woorden [1][0]"))


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

course = canvas.get_course(6580)
print(course.name)

assignment = course.get_assignment(93447)

print(assignment)

submissions = assignment.get_submissions()

# analyse submissions
countSubmissions=0
for submission in submissions:
    body = submission.body
    if (body):
        countSubmissions+=1
        analyseText(body)

for submission in submissions:
    # pprint(submission)
    body = submission.body
    if (body):
        score = scoreText(body, countSubmissions)
        print(strip_tags(body))
        print('Score:', score)
        print('-------------')

exit(1)

print('Read submissions:', countSubmissions)
for key, value in wordFreq.items():
    score=value*100/countSubmissions
    print(key, ' : ', value, ' : ', score)



#submission = assignment.get_submission(user_id)
for submission in submissions:
    thisPass=False
    print(submission.user_id)
    body = submission.body
    if (body):   
        print(body)
        analyseText(body)
        if ( matchText(body,"eerste [1][0]") ):
            thisPass=False
    if (hasattr(submission,'attachments')):
        for att in submission.attachments:
            print(att['display_name'], att['content-type'], att['size'])
            if ( att['size']> 40000 and att['content-type']=='application/pdf' ):
                thisPass=True

    if (thisPass):
        print(' *** Passed *** ')
        # submission.edit(submission={'posted_grade':10})
        # submission.edit(submission={'posted_grade':0})
        # submission.edit(submission={'entered_grade':'complete'})
        # (To Test) submission.edit(submission={'comments':'Automated rated'})

    print('\n------------------\n')

for key, value in wordFreq.items():
    if(value>4):
        print(key, ' : ', value)