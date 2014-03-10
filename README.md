新聞小幫手Backend
==================
http://newshelper.g0v.tw/
新聞小幫手的網頁以及 API Server
負責讓使用者回報錯誤新聞以及將錯誤資訊傳給 extension 用

如何開始
========
 1. 建立一個 mysql 資料庫

   `echo 'create database newshelper-db' | mysql -u root`

 1. 建立一組 mysql 資料庫帳號密碼

 1. 產生 config.php

   `cp config.php.sample config.php`

 1. 編輯 config.php 填入正確的資料庫帳號密碼、資料庫資訊

 1. 執行 `php ./_backend/webdata/prompt.php` 進入 prompt 模式

 1. 在 prompt 中執行以下語法

   `Report::createTable(); ReportChangeLog::createTable();`

 1. 如果以上沒有任何錯誤訊息，代表資料庫建立完成了。

    接下來請執行 `./build` 即可產成 _public 目錄

 1. 最後將 web server 的 document root 指到 _public 資料夾即可。

License
=======
MIT http://g0v.mit-license.org/
