01_ann_letters.php => analyze possible letters inside terms
02_analyze.php => compute common statistics (n-grams)
03_analyze_ann.php => compute statistics on the annotated terms (beginning words, ending words, inside words)
04_1_convert_stopwords.php => convert a stopwords.txt file into internal lists
NOTUSED 04_make_stoplist.php => alternative generation of stop words based on statistics
NOTUSED 05_make_candidates.php => compute candidate terms for larger n-grams
06_analyze_context.php => compute statistics on words before and after terms
07_analyze_common_parts.php => based on statistics tries to compute word roots and terminations (a kind of stemming based only on statistics)
08_analyze_ann_endings.php => identify word endings inside annotated terms based on statistics produced by 07
10_annotate.php => uses already produced statistics to actually annotate corpora

700_corenlp.php => uses Stanford CoreNLP to annotate corpora
710_corenlp_unannotated.php  => uses Stanford CoreNLP to annotate unannotated parts of the corpora

100_rake.php => uses RAKE to make annotations  (changes consists in the stopwords list used)
110_rake_original.php => uses RAKE to make annotations

200_tfidf_f.php => uses a standard TFIDF implementation to annotate
200_tfidf.php => uses a slightly modified TFIDF (based on paragraphs instead of files)

400_yake.php => uses YAKE to make annotations (changes consists in the stopwords list used)
410_yake_orig.php => uses YAKE to make annotations

500_textrank.php => uses TextRank to make annotations (changes consists in the stopwords list used)
510_textrank_orig.php => uses TextRank to make annotations

20_mytr.php => personal TextRank implementation
21_mytr_conll.php => personal TextRank implementation with variations
22_mytr_kcore.php => personal TextRank implementation with variations
23_mytr_kcore_nopos.php => personal TextRank implementation with variations

NOTUSED 300_make_conllu_train.php
NOTUSED 301_make_conllu_train_NN.php
NOTUSED 310_make_conllu_test.php

600_kl.php => Kullback Leibler divergence calculation and annotation
NOTUSED 601_make_kl_stoplist.php => alternative stoplist calculation based on Kullback Leibler divergence

1000_make_we_train.php => prepare for word embeddings based only on text
1010_make_we_conll_train.php  => prepare for word embeddings training based on the conllu annotations (tokens)
1020_make_we_conll_lemma_train.php => prepare for word embeddings training based on lemma (requires conllu annotations)
1030_make_word_lemma.php => extracts words lemma (without context)
./fasttext.sh => computes word embeddings using fasttext for the 3 produced training files

920_clus.php => clustering using OANC_Written embeddings
921_clus_crawl.php => clustering using common crawl pre-computed embeddings (300d-2M)
930_clus_my.php => clustering using computed embeddings (1010_make_we_conll_train.php)
940_clus_my_lemma.php => clustering using computed embeddings (1020_make_we_conll_lemma_train)

910_filter.php => filter terms starting with wrong characters (like comma)

800_postprocess.php => used to postprocess single word annotations

900_combine.php => computes combinations between various annotations (union, voting, etc.)
999_compare.php => displays scores for different annotations and combinations


1040_filter_radu.php  => transforms terms extracted with method2 to the general task format 


common_conll.php => utility functions for working with conll files
common_filter.php => utility functions for filtering terms
common.php => utility functions
test_common.php => tests for some functions