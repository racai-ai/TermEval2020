# -*- coding: utf-8 -*-
from corpus import Corpus
from embeddings import Embeddings
from keras.models import Sequential, Model
from keras.layers import Input, Dense, Activation, Dropout, LSTM, SimpleRNN, TimeDistributed, Flatten, Bidirectional, Concatenate
import numpy as np
from keras.models import model_from_json
from pathlib import Path
from mycorpus import MyCorpus
from keras.utils import plot_model

NUM_EPOCHS=50

#import os
#import locale
#os.environ["PYTHONIOENCODING"] = "utf-8"
#myLocale=locale.setlocale(category=locale.LC_ALL, locale="en_US.UTF8")

terms={}
infile=Path("../ACTER_version1_1_only_train/en/corp/annotations/corp_en_terms.ann").open(mode="r")
for line in infile:
    terms[line.strip()]=1
infile.close()

tfidf={}
infile=Path("../my/en/tfidf/corp/annotations_terms.ann").open(mode="r")
for line in infile:
    tfidf[line.strip()]=1
infile.close()

    
emb=Embeddings(
        fname="", # "/data/wordembeddings/cc.en.300.vec",
        ws="http://127.0.0.1:8023/wordvectors_get?w1={}",
        unknownStore="unknown.300.vec",
        embSize=300
        )
corpus1=MyCorpus(dir="corpus/en/corp",sampleSize=11,startToken=6,embeddings=emb,extension=".conll",forLSTM=True,terms=terms)
print(corpus1.fileList)
#corpus2=MyCorpus(dir="corpus/en/wind",sampleSize=11,startToken=6,embeddings=emb,extension=".conll",forLSTM=True,terms=terms)
#print(corpus2.fileList)

A1=Input(shape=(11,300),name="Words")
A2=Bidirectional(LSTM(500,return_sequences=True),name="A2")(A1)
A3=Dropout(0.2,name="A3")(A2)
A4=Flatten(name="A4")(A3)
A5=Dense(100, activation='relu',name="A5")(A4)

B1=Input(shape=(11,7),name="POS")
B2=Flatten(name="B2")(B1)
B3=Dense(100,activation='relu',name="B3")(B2)
#B2=Bidirectional(LSTM(5,return_sequences=True),name="B2")(B1)
#B3=Dropout(0.1,name="B3")(B2)
#B4=Flatten(name="B4")(B3)
#B5=Dense(5,activation='relu',name="B5")(B4)

#C1=Input(shape=(11,2),name="TFIDF")
#C2=Bidirectional(LSTM(10,return_sequences=True),name="C2")(C1)
#C3=Dropout(0.2,name="C3")(C2)
#C4=Flatten(name="C4")(C3)
#C5=Dense(10, activation='relu',name="C5")(C4)

O1=Concatenate(name="Combine")([A5,B3])
O2=Dense(100, activation="relu",name="O2")(O1)
#O3=Dropout(0.1,name="O3")(O2)
#O4=Dense(100, activation="relu",name="O4")(O2)
out=Dense(2,activation="softmax",name="OUT")(O2)

model=Model(inputs=[A1,B1],outputs=out)

#model=Sequential([
#1   LSTM(500,input_shape=(11,300),return_sequences=True),
#    Bidirectional(LSTM(500,return_sequences=True),input_shape=(11,300)),
#    Dropout(0.2),
#1    LSTM(50,return_sequences=True),
#    Flatten(),
#    Dense(300, activation='relu'),
#1    Dropout(0.1),
#1    Dense(1000,input_dim=9*300),
#1    Activation('relu'),
#1    Dropout(0.5),
#1    Dense(300),
#1    Activation('relu'),
#1    Dropout(0.1),
#    Dense(2, activation='softmax')
#])

model.summary()

model.compile(
    optimizer='adam',
    loss='categorical_crossentropy',
    metrics=['accuracy']
)


for epoch in range(0,NUM_EPOCHS):
    print("Start epoch %d / %d"%(epoch,NUM_EPOCHS))

    corpus1.endOfEpoch=False
    while(not corpus1.isEndOfEpoch()):
        (x,y)=corpus1.getBatch(5000)
        x=corpus1.adjustBatch(x)
        model.train_on_batch(x,np.array(y))

#    corpus2.endOfEpoch=False
#    while(not corpus2.isEndOfEpoch()):
#        (x,y)=corpus2.getBatch(5000)
#        x=corpus2.adjustBatch(x)
#        model.train_on_batch(x,np.array(y))

print("Evaluating")
corpus1.endOfEpoch=False
(x,y)=corpus1.getBatch(100000)
x=corpus1.adjustBatch(x)
ev=model.evaluate(x,np.array(y))
print(ev)
print(model.metrics_names)

print("Saving model")
model_json = model.to_json()
with open("model/model.json", "w") as json_file:
    json_file.write(model_json)
# serialize weights to HDF5
model.save_weights("model/model.h5")
print("Saved model to disk")

plot_model(model, to_file='model/model.png')
