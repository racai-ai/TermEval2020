<?php

require "common.php";
require "common_conll.php";

$fout=fopen("my/$CORPUSLANG/wordembeddings_conll_lemma.train","w");
$firstword=true;

function processFile($fname){
    global $fout,$firstword;
    
    echo "$fname\n";
    
    foreach(conll_getSentences(file_get_contents($fname)) as $sent){
	foreach(conll_getLemmaLower($sent) as $word){
		if(strlen($word)==0)continue;
		
		if($firstword)$firstword=false;
		else fwrite($fout," ");
		fwrite($fout,$word);
	}
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

fclose($fout);
