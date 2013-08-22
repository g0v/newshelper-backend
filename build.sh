#!/bin/sh

echo "[ ] start to build application"

echo "[*] installing required Node.js modules"
npm install

echo "[*] removing old generated files"
rm -rf _public

echo "[*] building static assets"
./node_modules/.bin/brunch build --optimize

echo "[*] copying files into _public/"
cp -rf _backend/* _public/
cp -rf app/webdata/* _public/webdata/
rm -rf app/webdata/*

echo "[+] build successfully"
