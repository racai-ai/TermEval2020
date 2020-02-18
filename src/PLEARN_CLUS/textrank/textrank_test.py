import summa
from summa.preprocessing.stopwords import LANGUAGES
import sys
from pathlib import Path

LANGUAGES["english"]=Path(sys.argv[2]).read_text()

text=Path(sys.argv[1]).read_text()

keywords = summa.keywords.keywords(text,ratio=0.3)
print(keywords)

#for kw in keywords:
#    print("%s;%f"%(kw[0],kw[1]))
