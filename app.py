# -*- coding: utf-8 -*-
from flask import Flask
from flask import request
from flask import jsonify
# from flaskext.mysql import MySQL

app = Flask(__name__)
app.debug = True
# mysql = MySQL()
# mysql.init_app(app)

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

  print title, newsLinks
  return '{"ok": true}'

if __name__ == "__main__":
  app.run()

