# -*- coding: utf-8 -*-
from flask import Flask
from flask import request
from flask import jsonify
from urlparse import urlparse
import MySQLdb

DATABASE_URL = "mysql://root@127.0.0.1/news"

app = Flask(__name__)
app.debug = True

db = MySQLdb.connect(host='127.0.0.1', user='root', db="news")
cursor = db.cursor()

@app.route("/")
def hello():
  return "Hello World!"

@app.route("/api/check_news", methods = ['GET'])
def check_news():
  params = request.args

  title = params.get("title", u"")
  body = params.get("body", u"")
  if (u"馬英九" in title) or (u"馬英九" in body):
    data = {
      "tags": ["解讀偏頗"],
      "description": "高村長表示，高鐵重開機並不能解決所有問題。",
      "proveLinks": [
        {"從高鐵延誤看被輕視的專業": "http://forum.businessweekly.com.tw/topic.aspx?fid=73&tid=4076"}
      ]
    }
    return jsonify(data)
  return '{"ok": true}', 404

@app.route("/api/report_news", methods = ['POST'])
def report_news():
  params = request.json
  title = params['title']
  newsLinks = params['newsLinks']
  description = params['description']

  news_links = params['newsLinks']
  prove_links = params['proveLinks']

  cursor.execute("INSERT INTO news (title, description) VALUES (%s, %s);", (title, description))
  news_id = db.insert_id()
  db.commit()

  for url in news_links:
    sql = "INSERT INTO news_links (news_id, url) VALUES (%s, %s);"
    cursor.execute(sql, (news_id, url))

  return '{"ok": true}'

if __name__ == "__main__":
  app.run()

