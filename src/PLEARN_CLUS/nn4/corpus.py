# -*- coding: utf-8 -*-
from pathlib import Path
from embeddings import Embeddings

class Corpus:

   # dir = directory with corpus files
   # sampleSize = the window size (for example 5)
   # startToken = position inside the window where to place the first word of a sentence (for example 2 => zero based)
   def __init__(self,dir,sampleSize,startToken,embeddings,extension,forLSTM=False):
      self.fileList=[]
      self.currentFileNum=0
      self.sampleSize=sampleSize
      self.startToken=startToken
      self.embeddings=embeddings
      self.currentLines=[]
      self.startSentence=["<s>","<s>","<s>","<s>","<s>","<s>","<s>","<s>","<s>","<s>"]
      self.endSentence=["</s>","</s>","</s>","</s>","</s>","</s>","</s>","</s>","</s>","</s>"]
      self.endOfEpoch=False
      self.currentOpenFile=None
      self.forLSTM=forLSTM

      if(len(dir)>0):
          self.fileList=self.getFileList(dir,extension) # read all filenames into memory
          self.currentOpenFile=self.fileList[self.currentFileNum].open(mode="r",encoding="utf8")

      self.addStartSentence()
      

   def addStartSentence(self):
      for i in range(0,self.startToken):
         self.currentLines.append(self.startSentence)
         
   def getFileList(self,dir,extension):
      ret=[]
      for p in Path(dir).iterdir():
         if(p.is_file() and p.name.endswith(extension)):
            ret.append(p)
      return ret
      
   def openNextFile(self):
      self.currentOpenFile.close()
      self.currentFileNum+=1
      if(self.currentFileNum>=len(self.fileList)):
         self.currentFileNum=0
         self.endOfEpoch=True
      self.currentOpenFile=self.fileList[self.currentFileNum].open(mode="r")
   

   def getMapX(self,line,index):
      return [line[1]]

   def getMapY(self,line,index):
      return [0]

   def getBatchX(self):
       b=[]
       for i in range(0,len(self.currentLines)):
           mapX=self.getMapX(self.currentLines[i],i)
           if(self.forLSTM):
               b.append(mapX)
           else:
               b.extend(mapX)
       
       return b

   def getBatchY(self):
       b=[]
       for i in range(0,len(self.currentLines)):
           b.extend(self.getMapY(self.currentLines[i],i))
       
       return b

   def isEndOfEpoch(self):
       return self.endOfEpoch


   def getBatch(self,num):
      batch_X=[]
      batch_Y=[]
      self.endOfEpoch=False
      while(len(batch_X)<num):
         line=self.currentOpenFile.readline()
         if(len(line)==0): # end of file
            self.openNextFile()    # open the next file
            self.currentLines=[]
            self.addStartSentence()   # add <s> tokens for half the sampleSize
            if(self.endOfEpoch):
                break;
                
            continue

         line=line.strip()
         if(line.startswith("#")): # ignore comments
            continue
            
         if(len(line)==0): # empty line = end of sentence
            if(len(self.currentLines)==self.startToken): # there was a sentence without tokens => ignore
                continue

            for i in range(self.startToken+1,self.sampleSize): # add </s> for the last part
                self.currentLines.append(self.endSentence)
                if(len(self.currentLines)>=self.sampleSize):
                    batch_X.append(self.getBatchX())
                    batch_Y.append(self.getBatchY())
                    self.currentLines.pop(0)

            self.currentLines=[]
            self.addStartSentence()   # add <s> tokens for half the sampleSize
            continue

         self.currentLines.append(line.split("\t"))
         
         if(len(self.currentLines)>=self.sampleSize):
             batch_X.append(self.getBatchX())
             batch_Y.append(self.getBatchY())
             self.currentLines.pop(0)

      return (batch_X,batch_Y)

   def getInputFromTokens(self,tokens):
      batch_X=[]
      self.currentLines=[]
      self.addStartSentence()   # add <s> tokens for half the sampleSize
      for i in range(0,len(tokens)):
          self.currentLines.append(tokens[i])
          if(len(self.currentLines)>=self.sampleSize):
              batch_X.append(self.getBatchX())
              self.currentLines.pop(0)

      for i in range(self.startToken+1,self.sampleSize): # add </s> for the last part
          self.currentLines.append(self.endSentence)
          if(len(self.currentLines)>=self.sampleSize):
             batch_X.append(self.getBatchX())
             self.currentLines.pop(0)

      return batch_X
  
   def createTokensFromSentence(self,sentence):
       tok1=sentence.split()
       tok=[]
       for i in range(0,len(tok1)):
           tok.append([i,tok1[i],'_','_','_','_','_','_','_','_'])
       return tok