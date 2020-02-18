# -*- coding: utf-8 -*-

from corpus import Corpus
from embeddings import Embeddings
from keras.models import Sequential, Model
from keras.layers import Input, Dense, Activation, Dropout, LSTM, SimpleRNN, TimeDistributed, Flatten, Bidirectional, Concatenate
import numpy as np
from keras.models import model_from_json
from pathlib import Path

class MyCorpus(Corpus):
    def __init__(self,dir,sampleSize,startToken,embeddings,extension,forLSTM=False,terms={},tfidf={}):
        self.terms=terms
        self.tfidf=tfidf
        super(MyCorpus, self).__init__(dir=dir,sampleSize=sampleSize,startToken=startToken,embeddings=embeddings,extension=extension,forLSTM=forLSTM)

    # INPUT mapping for a single word    
    def getMapX(self,line,index):
        a=self.embeddings.getEmbedding(line[1].lower())
        # CC, CD, JJ, NN, RB, V, O, SYM
        b=[0,0,0,0,0,0,0,0]
        if(line[3]=="CC"):              b[0]=1
        elif(line[3]=="CD"):            b[1]=1
        elif(line[3].startswith("JJ")): b[2]=1
        elif(line[3].startswith("NN")): b[3]=1
        elif(line[3].startswith("RB")): b[4]=1
        elif(line[3].startswith("V")):  b[5]=1
        elif(line[3]=="," or line[3]=="." or line[3]==";" or line[3]==":"): b[7]=1
        else: b[6]=1

        #c=self.embeddings.getEmbedding(line[2].lower())
        #c=[0,0]
        #if line[1].lower() in self.tfidf:
        #    c[0]=1
        #else:
        #    c[1]=1
        
        return [a,b] #,c]

    # OUTPUT mapping for a single word
    # should check index to be startToken
    def getMapY(self,line,index):
        if(index==self.startToken):
            ret=[0,0]
            if line[1].lower() in self.terms:
                ret[0]=1
            else:
                ret[1]=1
            return ret
        return []

    def adjustBatch(self,x):
        a=[]
        b=[]
        c=[]

        for i in range(0,len(x)):
            c_a=[]
            c_b=[]
            c_c=[]
            for j in range(0,len(x[i])):
                c_a.append(x[i][j][0])
                c_b.append(x[i][j][1])
#                c_c.append(x[i][j][2])
            a.append(c_a)
            b.append(c_b)
#            c.append(c_c)

        x=[np.array(a),np.array(b)] #,np.array(c)] 
        return x
