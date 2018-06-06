class DataRetriever(object):
    def __init__(self, db_connector):
        self.con = db_connector.connect()
	self.cur = self.con.cursor()

    def execute_update(self,update_sql,args):
	self.cur.execute(update_sql,args)
	self.con.commit()
        return self.cur.rowcount

    def execute_query(self,query_sql,args):
	self.cur.execute(query_sql,args)
	rows = self.cur.fetchall()
	results = []
	columns = [column[0] for column in self.cur.description]
	for row in rows:
	    results.append(dict(zip(columns,row)))

	print results
	return results

    def get_all_contacts(self):
        sql = 'select * from contact limit 10'
        return self.execute_query(sql, ())

    def get_all_contact_details_for_range(self,offset = 0):
	sql = 'select * from contact limit 10 offset '+offset
        return self.execute_query(sql, ())

    def get_all_contact_count(self):
	sql = 'select count(*) as count from contact'
        result = self.execute_query(sql, ())
	return result[0]['count']

    def check_user_exists(self,email):
	sql = 'select count(*) as count from contact where email = %s'
	result = self.execute_query(sql,(email,))
	return result[0]['count']

    def get_all_search_detail_count(self,search_string):
	sql = 'select count(*) as count from contact where email like %s or name like %s'
	search_string = '%'+search_string+'%'
	result = self.execute_query(sql,(search_string,search_string,))
        return result[0]['count']

    def get_all_search_details(self,search_string):
	sql = 'select * from contact where email like %s or name like %s limit 10'
        search_string = '%'+search_string+'%'
        return self.execute_query(sql,(search_string,search_string,))

    def get_all_search_details_for_range(self, search_string, offset = 0):
	sql = 'select * from contact where email like %s or name like %s limit 10 offset '+offset
        search_string = '%'+search_string+'%'
        return self.execute_query(sql,(search_string,search_string,))

    def delete_contact(self,email):
	sql = 'delete from contact where email = %s'
	return self.execute_update(sql,(email,))

    def update_contact(self,email,name):
	sql = 'update contact set name = %s where email = %s'
	return self.execute_update(sql,(name,email,))

    def add_contact(self,email,name):
	sql = 'insert into contact (email,name) VALUES (%s,%s)'
	return self.execute_update(sql, (email,name,))
