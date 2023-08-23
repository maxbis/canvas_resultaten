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

course_id = 12463
quiz_id = 8947

course = canvas.get_course(course_id)
quiz = course.get_quiz(quiz_id)

question_data = {
    'question_name': 'Vraag',
    'question_text': 'Deze vraag gaat over wat een variabele is. Welke uitspraak is waar?',
    'question_type': 'multiple_choice_question',
    'points_possible': 1,
    'answers': [{'answer_text': 'De waarde van een variabele kan je afdrukken', 'weight': 100},
                {'answer_text': 'De naam van de variabele kan je afdrukken', 'weight': 0}, 
                {'answer_text': 'De naam van de variabele in PHP begint altijd met een letter', 'weight': 0},
                {'answer_text': 'De waarde van een variabele kan je één keer aanpassen', 'weight': 0}]
}

question = quiz.create_question(question=question_data)
print("Added a new question with ID: ", question.id)