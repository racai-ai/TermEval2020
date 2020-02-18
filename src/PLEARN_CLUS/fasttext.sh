#!/bin/sh

/data/wordembeddings/fastText-0.1.0-mod/fasttext skipgram -input my/en/wordembeddings_conll_lemma.train -output my/en/wordembeddings_conll_lemma -minCount 1 -minn 1 -maxn 5 -ws 10

/data/wordembeddings/fastText-0.1.0-mod/fasttext skipgram -input my/en/wordembeddings_conll.train -output my/en/wordembeddings_conll -minCount 1 -minn 1 -maxn 5 -ws 10

/data/wordembeddings/fastText-0.1.0-mod/fasttext skipgram -input my/en/wordembeddings.train -output my/en/wordembeddings -minCount 1 -minn 1 -maxn 5 -ws 10
