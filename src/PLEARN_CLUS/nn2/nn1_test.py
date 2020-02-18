# -*- coding: utf-8 -*-
from corpus import Corpus
from embeddings import Embeddings
import numpy as np
from keras.models import model_from_json
from mycorpus import MyCorpus
from pathlib import Path


words=[]

class MyCorpusTest(MyCorpus):
    def getMapX(self,line,index):
        if(index==self.startToken):
            words.append(line[1])
        return super(MyCorpusTest,self).getMapX(line,index)
            
    def getMapY(self,line,index):
        return []

emb=Embeddings(
        fname="", # "/data/wordembeddings/cc.en.300.vec",
        ws="http://127.0.0.1:8023/wordvectors_get?w1={}",
        unknownStore="unknown.300.vec",
        embSize=300
        )

print("Loading saved model")
json_file = open('model/model.json', 'r')
loaded_model_json = json_file.read()
json_file.close()
model = model_from_json(loaded_model_json)
print("Loading weights")
model.load_weights("model/model.h5")
print("Loaded model from disk")

for corpus in ["corp","equi","wind"]:
    print ("Processing corpus %s"%(corpus))

    c=MyCorpusTest(dir="corpus/en/"+corpus,sampleSize=11,startToken=6,embeddings=emb,extension=".conll",forLSTM=True,terms={})
    print(c.fileList)


    fout=Path("../my/en/nn2/"+corpus+"/annotations_terms.ann").open(mode="w")

    c.endOfEpoch=False
    terms={}
    while(not c.isEndOfEpoch()):
        words.clear()
        (x,y)=c.getBatch(5000)
        x=c.adjustBatch(x)
        #y=model.predict_classes(x).tolist()
        y=model.predict(x)
        for i in range(0,len(y)):
            cls=np.argmax(y[i])
            if(cls==0):
                terms[words[i]]=1

    for k,v in terms.items():
        fout.write(k.lower()+"\n")

    fout.close()
