<?php

require "common.php";

$ann=[];

function processFile($fname){
    global $ann,$CORPUSLANG;
    
    echo "$fname\n";

    shell_exec("python textrank/textrank_test.py $fname my/$CORPUSLANG/stop_words.txt > tmp.tr.txt");
    
    $data=loadList("tmp.tr.txt");
    foreach($data as $v=>$k){
	if(!isset($ann[$k]))$ann[$k]=$v;
	else $ann[$k]+=$v;
    }
    @unlink("tmp.tr.txt");
}

@mkdir("my");
@mkdir("my/$CORPUSLANG/trank");

foreach(["equi","wind","corp","hf"] as $corpus){
    @mkdir("my/$CORPUSLANG/trank/$corpus");
    $ann=[];
    $num=processFolder("$CORPUSBASE/$CORPUSLANG/$corpus/texts/annotated/");
    arsort($ann);
    //foreach($ann as $k=>$v){if($v<1)unset($ann[$k]);}
    saveList($ann,"my/$CORPUSLANG/trank/$corpus/annotations_terms.ann");
}
