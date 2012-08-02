# encoding: utf-8
import os

intext = "in.txt"
mecab_pos = "out.txt"
os.system("mecab -Ochasen "+ intext + " > "+ mecab_pos)

