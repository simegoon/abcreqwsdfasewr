# -*- coding: utf-8 -*-
import httplib2
from urllib import parse
import json
from pprint import pprint

def insertRecode(table,recode,key=""):
	url = "https://mygood-ea08e.firebaseio.com/"
	req_type = "PUT"
	if key == "":
		trul = url + table +".json"
		req_type = "POST"
	else:
		trul = url + table +"/"+recode[key]+".json"
	_http = httplib2.Http(timeout=15)
	_response, content = _http.request(trul, req_type, json.dumps(recode))
	print(_response)
	print( content.decode())
	if _response['status'] != '200':
		return False;
	return True;


def createIndex(table,indexs):
	url = "https://mygood-ea08e.firebaseio.com/"
	trul = url + table +".json"
	_http = httplib2.Http(timeout=15)

	print(json.dumps({".indexOn":[indexs]}).encode("utf-8"))
	_response, content = _http.request(trul, "PATCH", json.dumps({'".indexOn"':indexs}).encode('utf-8'),headers = {"content-type": "application/json; charset=UTF-8"})
	print(_response)
	print( content.decode())
	if _response['status'] != '200':
		return False;
	return True;

#createIndex("test/sendf","from")


def getRecode(table,where={}):
	url = "https://mygood-ea08e.firebaseio.com/"
	trul = url + table +".json?"+parse.urlencode(where)
	_http = httplib2.Http(timeout=15)
	_response, content = _http.request(trul, 'GET')
	return json.loads(content.decode());


if insertRecode("test/sendf",{"from_name":u"周晓媛","from":"26w200","query_times":"1"}):
	print("insert ok!")
else:
	print("insert err!")

pprint(getRecode("test/sendf",{"limitToFirst":2,"orderBy":'"from"',"equalTo":"1"}))