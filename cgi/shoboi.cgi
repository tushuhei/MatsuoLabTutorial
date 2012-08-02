#!/usr/local/bin/python
# encoding:utf-8

import os 
import cgi
import commands

if 'QUERY_STRING' in os.environ:
    query = cgi.parse_qs(os.environ['QUERY_STRING'])["query"][0]
else:
    query = "今日も快晴です"
result = commands.getoutput("echo "+query+" | /usr/local/bin/mecab").replace("\n","<br>")

print "Content-Type: text/html; charset=utf-8"
print
html =  """
<html>
    <head>
        <meta charset='utf8'>
        <title> しょぼい CGI </title>
    </head>
    <body>
        ここに気になるワードを入れてね
        <form action='#' method='GET'>
        <textarea name='query'></textarea>
        <input type='submit'>
        </form>
        <hr>
        %s
    </body>
</html>
"""
print html%(result)
