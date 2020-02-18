import yake
import sys
from pathlib import Path

text=Path(sys.argv[1]).read_text()

language = sys.argv[2]
max_ngram_size = 5
deduplication_thresold = 0.9
deduplication_algo = 'seqm'
#deduplication_algo = 'levs'
#deduplication_algo = 'jaro'
windowSize = 1
numOfKeywords = 100

custom_kw_extractor = yake.KeywordExtractor(lan=language, n=max_ngram_size, dedupLim=deduplication_thresold, dedupFunc=deduplication_algo, windowsSize=windowSize, top=numOfKeywords, features=None)
keywords = custom_kw_extractor.extract_keywords(text)

for kw in keywords:
    print("%s;%f"%(kw[0],kw[1]))
