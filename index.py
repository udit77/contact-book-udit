import hashlib
import json
from functools import wraps
from flask import Flask, Response, request, render_template
from flaskext.mysql import MySQL
from flask_cors import CORS

from config import *
from db_connect import DataRetriever
app = Flask(__name__)

cors = CORS(app, resources={r"/*": {"origins": "*"}})

db = MySQL()
app.config['MYSQL_DATABASE_USER'] = MYSQL_DATABASE_USER
app.config['MYSQL_DATABASE_PASSWORD'] = MYSQL_DATABASE_PASSWORD
app.config['MYSQL_DATABASE_DB'] = MYSQL_DATABASE_DB
app.config['MYSQL_DATABASE_HOST'] = MYSQL_DATABASE_HOST
db.init_app(app)


data_retriever = DataRetriever(db)

def requires_auth(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        auth = request.headers.get('Authorization')
        if not auth or hashlib.sha1('plivo123').hexdigest() != auth :
            return Response(json.dumps({'msg':'Unauthorized.'}), status=401, mimetype='application/json')
        return f(*args, **kwargs)
    return decorated


'''@app.route('/')
@requires_auth
def hello_workd():
   return '{"total":23,"data":[{"email":"aayushops@gmail.com","name":"Aayush Kumar Shukla"},{"email":"Abhishekchy@yahoo.in","name":"Abhishek Kumar"},{"email":"abhishekdn@gmail.com","name":"Abhishek"},{"email":"adminsuman33@gmail.com","name":"Suman Anand"},{"email":"agarwalabhishek@bit.in","name":"Abhishek Agarwal"},{"email":"aman77@gmail.com","name":"Aman Raj"},{"email":"aminabhi@gmail.com","name":"Abhishek Mishra"},{"email":"anuragece@yahoo.in","name":"Anurag Anand"},{"email":"Anurrudh55@gmail.com","name":"Anurudh Kumar"},{"email":"Ashishcub@yahoo.in","name":"Ashish"}]}'
'''

@app.route('/contacts', methods = ['GET'])
@requires_auth
def get_all_contacts():
   result = {
       'total':0,
       'data':[]
   }
   result['total'] = data_retriever.get_all_contact_count()
   if result['total']:
       result['data'] = data_retriever.get_all_contacts()
   return json.dumps(result)


@app.route('/contacts/range', methods = ['GET'])
@requires_auth
def get_all_contact_details_for_range():
   offset = request.args.get('offset')
   result = {
       'data':[]
   }
   result['data'] = data_retriever.get_all_contact_details_for_range(offset)
   return json.dumps(result)


@app.route('/search', methods = ['GET'])
@requires_auth
def search():
   search_string = request.args.get('search_string')
   result = {
       'total':0,
       'data':[]
   }
   result['total'] = data_retriever.get_all_search_detail_count(search_string)
   if result['total']:
       result['data'] = data_retriever.get_all_search_details(search_string)
   return json.dumps(result)


@app.route('/search/range')
@requires_auth
def search_by_range():
   search_string = request.args.get('search_string')
   offset = request.args.get('offset')
   result = {
       'data':[]
   }
   result['data'] = data_retriever.get_all_search_details_for_range(search_string,offset)
   return json.dumps(result)


@app.route('/add', methods = ['POST'])
@requires_auth
def add_contact():
   data = request.get_json()
   user_count = data_retriever.check_user_exists(data['email'])
   if user_count:
	return Response(json.dumps({'msg':'Email already exists.'}), status=403, mimetype='application/json')
   else:
	try:
	    data_retriever.add_contact(data['email'],data['name'])
	    return Response(json.dumps({'status':'SUCCESS','msg':'Contact successfully added.'}), status=200, mimetype='application/json')
    	except Exception as e:
	    return Response(json.dumps({'msg':'Error occured.Please try again.'}), status=500, mimetype='application/json')



@app.route('/update', methods = ['POST'])
@requires_auth
def update_contact():
   data = request.get_json()
   try:
       data_retriever.update_contact(data['email'],data['name'])
       return Response(json.dumps({'status':'SUCCESS','msg':'Contact successfully updated.'}), status=200, mimetype='application/json')
   except Exception as e:
       print e
       return Response(json.dumps({'msg':'Error occured.Please try again.'}), status=500, mimetype='application/json')



@app.route('/delete', methods = ['POST'])
@requires_auth
def delete_contact():
   data = request.get_json()
   try:
       data_retriever.delete_contact(data['email'])
       return Response(json.dumps({'status':'SUCCESS','msg':'Contact successfully deleted.'}), status=200, mimetype='application/json')
   except Exception as e:
       print e
       return Response(json.dumps({'msg':'Error occured.Please try again.'}), status=500, mimetype='application/json')


if __name__ == '__main__':
    app.run(host='0.0.0.0')
