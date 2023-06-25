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

course_id = 12630
quiz_id = 8438

course = canvas.get_course(course_id)
quiz = course.get_quiz(quiz_id)

question_data = {
    'question_name': 'First Question',
    'question_text': 'What is the capital of France?',
    'question_type': 'multiple_choice_question',
    'points_possible': 1,
    'answers': [{'answer_text': 'Paris', 'weight': 100}, {'answer_text': 'Berlin', 'weight': 0},  {'answer_text': 'Nice', 'weight': 0},  {'answer_text': 'Bordeaux', 'weight': 0}]
}

question = quiz.create_question(question=question_data)
print("Added a new question with ID: ", question.id)