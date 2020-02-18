# -*- coding: utf-8 -*-
from sklearn import tree
from pathlib import Path
from corpus import Corpus
import numpy as np

def loadList(fname):
    ret={}
    infile=Path(fname).open(mode="r")
    for line in infile:
        s=line.strip()
        if(len(s)>0):
            ret[line.strip()]=1
    infile.close()
    return ret

def getFileList(dir,extension):
    ret=[]
    for p in Path(dir).iterdir():
        if(p.is_file() and p.name.endswith(extension)):
            ret.append(p)
    return ret

def loadTerms(fname):
    t=loadList(fname)
#    ret={}
#    for a in t.keys():
#        for w in a.split(" "):
#            ret[w]=1
#    return ret
    return t

tfidf_terms=loadTerms("../my/en/tfidff/corp/annotations_terms.ann")
mytr_terms=loadTerms("../my/en/mytr/corp/annotations_terms.ann")
rakeo_terms=loadTerms("../my/en/rakeo/corp/annotations_terms.ann")
yakeo_terms=loadTerms("../my/en/yakeo/corp/annotations_terms.ann")
trank_terms=loadTerms("../my/en/trank/corp/annotations_terms.ann")
my_terms=loadTerms("../my/en/my/corp/annotations_terms.ann")
nn1_terms=loadTerms("../my/en/nn1/corp/annotations_terms.ann")
stopwords=loadList("../my/en/stop_words.txt")

comb_terms=loadTerms("../my/en/mytr_rakeo_yakeo_tfidf/corp/annotations_terms.ann")

POS={"CC":1,"CD":2,"DT":3,"EX":4,"FW":5,"IN":6,"JJ":7,"JJR":8,
    "JJS":9,"LS":10,"MD":11,"NN":12,"NNS":13,"NNP":14,"NNPS":15,"PDT":16,
    "POS":17,"PRP":18,"PRP$":19,"RB":20,"RBR":21,"RBS":22,"RP":23,
    "SYM":26,"TO":27,"UH":28,"VB":29,"VBD":30,"VBG":31,"VBN":32,
    "VBP":33,"VBZ":34,"WDT":35,"WP":36,"WP$":37,"WRB":38}

terms=[]

class MyCorpus(Corpus):
    def getMapX(self,line,index):
        ret=[]

        if(line[3] in POS.keys()): ret.append(POS[line[3]])
        else: ret.append(POS["SYM"])
        
        if(line[1].lower() in tfidf_terms.keys()): ret.append(1)
        else: ret.append(0)

        if(line[1].lower() in mytr_terms.keys()): ret.append(1)
        else: ret.append(0)

        if(line[1].lower() in comb_terms.keys()): ret.append(1)
        else: ret.append(0)

        if(line[1].lower() in rakeo_terms.keys()): ret.append(1)
        else: ret.append(0)

        if(line[1].lower() in stopwords.keys()): ret.append(1)
        else: ret.append(0)

#        if(line[1].lower() in yakeo_terms.keys()): ret.append(1)
#        else: ret.append(0)

        if(line[1].lower() in my_terms.keys()): ret.append(1)
        else: ret.append(0)

#        if(line[1].lower() in trank_terms.keys()): ret.append(1)
#        else: ret.append(0)

        if(line[1].lower() in nn1_terms.keys()): ret.append(1)
        else: ret.append(0)
        
        
        return ret

    def getMapY(self,line,index):
        if(index==self.startToken+2):
            if(self.currentLines[index-2][1].lower()+" "+self.currentLines[index-1][1].lower()+" "+line[1].lower() in terms.keys()):
                return [1]
            else:
                return [0]
        return []

testWords=[]
class MyCorpusTest(MyCorpus):
    def getMapX(self,line,index):
        if(index==self.startToken+2):
            testWords.append(self.currentLines[index-2][1].lower()+" "+self.currentLines[index-1][1].lower()+" "+line[1].lower())
        return super(MyCorpusTest,self).getMapX(line,index)
        
    def getMapY(self,line,index):
        return []

dataX=[]
dataY=[]

for corpus in ["corp","wind"]:
    terms=loadList("../ACTER_version1_1_only_train/en/%s/annotations/%s_en_terms.ann"%(corpus,corpus))
    
    c=MyCorpus(dir="../my/en/corenlp/%s/"%(corpus),sampleSize=7,startToken=2,embeddings=[],extension=".conll",forLSTM=False)
    
    c.endOfEpoch=False
    while(not c.isEndOfEpoch()):
        (x,y)=c.getBatch(5000)
        y=np.array(y).reshape(len(y)).tolist()
        dataX.extend(x)
        dataY.extend(y)
    
    print(len(dataX))
    print(len(dataY))
    
dt=tree.DecisionTreeClassifier()
print("Training")
dt.fit(dataX,dataY)

foundTerms={}

for corpus in ["corp","wind","equi"]:
    print("Running on %s"%corpus)
    foundTerms={}
    c=MyCorpusTest(dir="../my/en/corenlp/%s/"%(corpus),sampleSize=7,startToken=2,embeddings=[],extension=".conll",forLSTM=False)

    
    c.endOfEpoch=False
    while(not c.isEndOfEpoch()):
        testWords=[]
        (x,y)=c.getBatch(5000)
        y=dt.predict(x)
        for i in range(0,len(y)):
            if(y[i]==1):
                foundTerms[testWords[i]]=1

    fout=Path("../my/en/dt3/%s/annotations_terms.ann"%(corpus)).open(mode="w")
    for t in foundTerms.keys():
        fout.write(t+"\n")
    fout.close()
