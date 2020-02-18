<?php

require "common.php";

$ann=[];

function processFile($fname){
    global $ann,$CORPUSLANG;
    
    echo "$fname\n";

    shell_exec("python yake_test/yake_orig.py $fname $CORPUSLANG > tmp.yakeo.txt");
    
    $data=loadStat("tmp.yakeo.txt");
    foreach($data as $k=>$v){
	if(!isset($ann[$k]))$ann[$k]=$v;
	else $ann[$k]+=$v;
    }
    @unlink("tmp.yakeo.txt");
}

@mkdir("my");
@mkdir("my/$CORPUSLANG/yakeo");

foreach(["equi","wind","corp","hf"] as $corpus){
    @mkdir("my/$CORPUSLANG/yakeo/$corpus");
    $ann=[];
    $num=processFolder("$CORPUSBASE/$CORPUSLANG/$corpus/texts/annotated/");
    arsort($ann);
    //foreach($ann as $k=>$v){if($v<1)unset($ann[$k]);}
    saveList($ann,"my/$CORPUSLANG/yakeo/$corpus/annotations_terms.ann");
}
