# encoding: utf-8
import os
import math

# POSファイル集合があるディレクトリ
pos_dir = "pos_dir"
# ファイル数変数
N = 0
# DFハッシュ
df = {}
# TFハッシュ
tf = {}

# POSファイル集合があるディレクトリからファイルを読み込み
for posfile in os.listdir(pos_dir):
    # ファイルから一行読み込み、行の先頭(形態素)を切り出す
    tf[posfile] = {}
    for line in open(pos_dir+"/"+posfile, "r"):
        word = line.split("\t")[0].rstrip()
        if word:
            if not df.has_key(word):
                df[word] = set()
            df[word].add(posfile) 

            if not tf[posfile].has_key(word):
                tf[posfile][word] = 0
            tf[posfile][word] += 1
    # ファイル数カウント
    N += 1

# TFIDFの計算
for key in tf.keys():
    print "document:%(key)s"%locals()
    for word in tf[key].keys():
        tfv = tf[key][word]
        dfv = len(df[word])
        idfv = math.log(N/dfv)
        tfidf = tfv * idfv
        print "term:%(word)-20s\ttf:%(tfv)-5d\tdf:%(dfv)-5d\tidf:%(idfv)-10f\tfidf:%(tfidf)-10f"%locals()
