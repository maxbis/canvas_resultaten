from canvasapi import Canvas
import configparser

config = configparser.ConfigParser()
if ( not (config.read("../import/canvas.ini") or config.read("canvas.ini"))):
    print()
    dd('Error: canvas.ini not found')

# Canvas API URL
API_URL = config.get('main', 'host')
# Canvas API key
API_KEY = config.get('main', 'api_key')

canvas = Canvas(API_URL, API_KEY)

course_id = 12629
quiz_id = 8852

course = canvas.get_course(course_id)
quiz = course.get_quiz(quiz_id)

question_data = {
    'question_name': 'Vraag',
    'question_text': 'Waarom hebben we in de ICT een binair (twee tallig) stelsel?',
    'question_type': 'multiple_choice_question',
    'points_possible': 1,
    'answers': [{'answer_text': 'Omdat de computer alleen een 0 of een 1 herkent.', 'weight': 100},
                {'answer_text': 'Omdat dat eenvoudiger is met rekenen.', 'weight': 0}, 
                {'answer_text': 'Omndat dat energoe bespaart.', 'weight': 0},
                {'answer_text': 'Omdat je daarmee makkelijker graphics en kleuren kunt coderen.', 'weight': 0}]
}

question = quiz.create_question(question=question_data)
print("Added a new question with ID: ", question.id)