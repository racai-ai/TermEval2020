# -*- coding: utf-8 -*-
import zipfile
from pathlib import Path
import urllib.request
import urllib.parse

class Embeddings:
    def __init__(self,fname,ws="",unknownStore="",embSize=0):
        self.ws=ws
        self.unknown=[]
        self.startSentenceEmbedding=[]
        self.startSentenceWord="<s>"
        self.endSentenceEmbedding=[]
        self.endSentenceWord="</s>"
        self.embeddings={}
        self.embeddingsSize=embSize
        self.unknownStore=unknownStore
        
        self.loadEmbeddings(fname)
        if(len(self.unknownStore)>0):
            p=Path(self.unknownStore)
            if(p.is_file()):
                self.loadEmbeddingsFromText(self.unknownStore)
        
        for i in range(0,self.embeddingsSize):
            self.unknown.append(0.0)
            self.startSentenceEmbedding.append(-1.0)
            self.endSentenceEmbedding.append(1.0)
        
    def loadEmbeddings(self,fname):
        
        if(len(fname)==0):
            return False

        self.embeddings={}
        self.embeddingsSize=0
        
        if(fname.find(".zip/")>0):
            archive=fname[0:fname.find(".zip/")+4]
            path=fname[fname.find(".zip/")+5:]
            self.loadEmbeddingsFromZIP(archive,path)
        else:
            self.loadEmbeddingsFromText(fname)
            
            
    def loadEmbeddingsFromZIP(self,archive,path):
        print("Loading word embeddings from archive [%s], file [%s]"%(archive,path))
        archiveFile = zipfile.ZipFile(archive, 'r')
        dataFile = archiveFile.open(path,'r')
        lnum=0
        while(True):
            line=dataFile.readline()
            lnum+=1
            if(len(line)==0): # end of file
                break
            
            line=line.strip()
            if(len(line)==0): # empty line
                continue
            
            data=line.split()
            if(lnum==1):
                print("Reading %s entries with size %s"%(data[0].decode(),data[1].decode()))
                self.embeddingsSize=int(data[1].decode())
                continue
            
            if(len(data)!=self.embeddingsSize+1):
                print("Invalid embeddings size from file for %s (got %d expecting %d)"%(data[0].decode(),len(data)-1,self.embeddingsSize))
                continue
            
            self.embeddings[data[0].decode()]=[float(x.decode()) for x in data[1:]]
            
        dataFile.close()
        print("Embeddings loaded")
        
    def loadEmbeddingsFromText(self,fname):
        print("Loading word embeddings from text file [%s]"%(fname))
        dataFile = Path(fname).open(mode='r',encoding="utf8")
        lnum=0
        while(True):
            line=dataFile.readline()
            lnum+=1
            if(len(line)==0): # end of file
                break
            
            line=line.strip()
            if(len(line)==0): # empty line
                continue
            
            data=line.split()
            if(lnum==1):
                disp=data[0]
                if(data[0]=="0"): disp="??"
                print("Reading %s entries with size %s"%(disp,data[1]))
                self.embeddingsSize=int(data[1])
                continue
            
            if(len(data)!=self.embeddingsSize+1):
                print("Invalid embeddings size from file for %s (got %d expecting %d)"%(data[0],len(data)-1,self.embeddingsSize))
                continue
            
            self.embeddings[data[0]]=[float(x) for x in data[1:]]
            
        dataFile.close()
        print("Embeddings loaded")


    def getEmbedding(self,word):
        # dictionary lookup
        if(word in self.embeddings):
            return self.embeddings[word]
        
        if(word==self.startSentenceWord):
            return self.startSentenceEmbedding

        if(word==self.endSentenceWord):
            return self.endSentenceEmbedding
        
        # try via web service
        if(len(self.ws)>0 and self.ws.startswith("http")):
            url=self.ws.format(urllib.parse.quote(word))
            print(url)
            contents = urllib.request.urlopen(url).read()
            contents=contents.decode().strip()
            if(contents.startswith("ERROR") or len(contents)==0):
                print("Error retrieving embeddings for %s"%word)
                self.embeddings[word]=self.unknown
                return self.unknown
            
            data=contents.split()
            if(len(data)!=self.embeddingsSize+1):
                print("Invalid embeddings size via web service for %s (got %d expecting %d)"%(word,len(data)-1,self.embeddingsSize))
                self.embeddings[word]=self.unknown
                return self.unknown
            
            self.embeddings[data[0]]=[float(x) for x in data[1:]]
            
            if(len(self.unknownStore)>0):
                p=Path(self.unknownStore)
                if(not p.is_file()):
                    f=p.open(mode='w')
                    f.write("0 "+str(self.embeddingsSize)+"\n")
                else:
                    f=p.open(mode='a+')
                f.write(contents.strip()+"\n")
                f.close()
            
            return self.embeddings[data[0]]
        
        return self.unknown
    
#emb=Embeddings("embeddings/corola.100.50.vec.zip/corola.100.50.vec","http://89.38.230.23/word_embeddings/ws/wordvectors_get.php?w1={}")
#emb=Embeddings("embeddings/corola.100.50.vec")
#print(emb.getEmbedding("de"))
#print(emb.getEmbedding("sadsadas"))
#print(emb.getEmbedding("sadsadas"))
