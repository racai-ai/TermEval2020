<?php

require "common.php";

$ann=[];

function processFile($fname){
    global $ann;
    
    echo "$fname\n";

    shell_exec("python yake_test/yake_test.py $fname > tmp.txt");
    
    $data=loadStat("tmp.txt");
    foreach($data as $k=>$v){
	if(!isset($ann[$k]))$ann[$k]=$v;
	else $ann[$k]+=$v;
    }
    @unlink("tmp.txt");
}

@mkdir("my");
@mkdir("my/$CORPUSLANG/yake");

file_put_contents("venv_yake/lib/python3.7/site-packages/yake/StopwordsList/stopwords_enauto.txt",file_get_contents("my/$CORPUSLANG/stop_words.txt"));

foreach(["equi","wind","corp","hf"] as $corpus){
    @mkdir("my/$CORPUSLANG/yake/$corpus");
    $ann=[];
    $num=processFolder("$CORPUSBASE/$CORPUSLANG/$corpus/texts/annotated/");
    arsort($ann);
    //foreach($ann as $k=>$v){if($v<1)unset($ann[$k]);}
    saveList($ann,"my/$CORPUSLANG/yake/$corpus/annotations_terms.ann");
}
