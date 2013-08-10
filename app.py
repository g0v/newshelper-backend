# -*- coding: utf-8 -*-
from flask import Flask
from flask import request
from flask import jsonify
from urlparse import urlparse
import os
import MySQLdb
import re

DATABASE_URL = "mysql://root@127.0.0.1/news"

app = Flask(__name__, static_folder='static', static_url_path='')
app.debug = True

db_url = os.getenv('DATABASE_URL')
if db_url!=None:
  matched = re.match(r'^mysql://([^:]*):([^@]*)@([^/]*)/(.*)$', db_url)
  username, password, host, db = matched.groups()
  db = MySQLdb.connect(host=host, user=username, passwd=password, db=db)
else:
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
  elif  (u"蔡英文" in title) or (u"蔡英文" in body):
    data = {
      "tags": ["斷章取義"],
      "description": "品質差的資訊傳遞",
      "proveLinks": [
        {"來不及說出的心聲：蜜朵麗冰淇淋想給中天電視台記者的一封信": "http://www.newsmarket.com.tw/blog/16512/"}
      ]
    }
    return jsonify(data)
  elif (u"國防部" in title) or (u"國防部" in body):
    data = {
      "tags": ["解讀偏頗"],
      "description": "實際有用的資訊傳遞",
      "proveLinks": [
        {"公民1985行動聯盟": "http://www.org.tw/"}
      ]
    }
    return jsonify(data)
  elif (u"狂犬病" in title) or (u"狂犬病" in body):
    data = {
      "tags": ["內容錯誤"],
      "description": "實際有用的資訊傳遞",
      "proveLinks": [
        {"科學事實": "http://tw.news.yahoo.com/%E7%95%8F%E5%85%89-%E6%81%90%E6%B0%B4-%E6%94%BB%E6%93%8A-%E7%8B%82%E7%8A%AC%E7%97%85%E7%97%87%E7%8B%803%E6%9C%9F-111901271.html"}
      ]
    }
    return jsonify(data)

  return '{"ok": true}', 404

@app.route("/api/report_news", methods = ['POST'])
def report_news():
  # params = request.json
  # title = params['title']
  # newsLinks = params['newsLinks']
  # description = params['description']
  # news_links = params['newsLinks']
  # prove_links = params['proveLinks']

  title = request.form.get('title')
  description = request.form.get('description')
  news_links = [request.form.get('link')]
  prove_links = [{request.form.get('proveTitle'): request.form.get('proveLink')}]

  cursor.execute("INSERT INTO news (title, description) VALUES (%s, %s);", (title, description))
  news_id = db.insert_id()

  for url in news_links:
    sql = "INSERT INTO news_links (news_id, url) VALUES (%s, %s);"
    cursor.execute(sql, (news_id, url))

  for prove in prove_links:
    title = prove.iterkeys().next()
    url = prove[title]
    sql = "INSERT INTO prove_links (news_id, title, url) VALUES (%s, %s, %s);"
    cursor.execute(sql, (news_id, title, url))
  db.commit()

  return '{"ok": true}'

@app.route("/api/get_reports", methods = ['GET'])
def get_reports():
  cursor.execute("SELECT * FROM news_links WHERE ('timestamp' > DATE_SUB(now(), INTERVAL 3 DAY)")

  gets = cursor.fetchall()
  results = []
  for row in gets:
      results.append(
          {"title": row[0], "link": row[1]}
      )

  data = {
    "count": 3,
    "result": results
  }

  return jsonify(data)

if __name__ == "__main__":
  app.run()

