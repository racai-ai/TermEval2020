<?php

require "common.php";
require "common_conll.php";

$wlemma=[];

function processFile($fname){
    global $wlemma;
    
    echo "$fname\n";
    
    foreach(conll_getSentences(file_get_contents($fname)) as $sent){
	foreach($sent as $tok)$wlemma[mb_strtolower($tok['word'])]=mb_strtolower($tok['lemma']);
    }
}

$num=0;
$num+=processFolder("my/$CORPUSLANG/corenlp/equi/",".conll");
$num+=processFolder("my/$CORPUSLANG/corenlp/corp/",".conll");
$num+=processFolder("my/$CORPUSLANG/corenlp/wind/",".conll");
$num+=processFolder("my/$CORPUSLANG/corenlp/hf/",".conll");
$num+=processFolder("my/$CORPUSLANG/corenlp_unannotated/equi/",".conll");
$num+=processFolder("my/$CORPUSLANG/corenlp_unannotated/corp/",".conll");
$num+=processFolder("my/$CORPUSLANG/corenlp_unannotated/wind/",".conll");

saveStat($wlemma,"my/$CORPUSLANG/words_lemma.txt");