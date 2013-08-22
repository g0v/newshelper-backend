#!/bin/sh

echo "[ ] start to build application"

echo "[*] installing required Node.js modules"
npm i

echo "[*] removing old generated files"
rm -rf _public

echo "[*] copying backend to _public/"
cp -rf _backend _public

echo "[*] building static assets"
./node_modules/.bin/brunch b -o

echo "[*] copying asset files into _public/"
cp -rf app/webdata/* _public/webdata/
rm -rf app/webdata
find _public/ -type f -name '*.phtml.html' -exec sh -c 'mv "$1" "${1%.phtml.html}.phtml"' _ {} \;

echo "[+] build successfully, please deploy _public/"
