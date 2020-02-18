import summa
from summa.preprocessing.stopwords import LANGUAGES
import sys
from pathlib import Path


lang="english"
if(sys.argv[2]=="en"):
    lang="english"
else:
    print("Unknown language")
    exit()

text=Path(sys.argv[1]).read_text()

keywords = summa.keywords.keywords(text,ratio=0.3)
print(keywords)

#for kw in keywords:
#    print("%s;%f"%(kw[0],kw[1]))
