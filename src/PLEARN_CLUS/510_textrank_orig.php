<?php

require "common.php";

$ann=[];

function processFile($fname){
    global $ann,$CORPUSLANG;
    
    echo "$fname\n";

    shell_exec("python textrank/textrank_orig.py $fname $CORPUSLANG > tmp.tro.txt");
    
    $data=loadList("tmp.tro.txt");
    foreach($data as $v=>$k){
	if(!isset($ann[$k]))$ann[$k]=$v;
	else $ann[$k]+=$v;
    }
    @unlink("tmp.tro.txt");
}

@mkdir("my");
@mkdir("my/$CORPUSLANG/tranko");

foreach(["equi","wind","corp","hf"] as $corpus){
    @mkdir("my/$CORPUSLANG/tranko/$corpus");
    $ann=[];
    $num=processFolder("$CORPUSBASE/$CORPUSLANG/$corpus/texts/annotated/");
    arsort($ann);
    //foreach($ann as $k=>$v){if($v<1)unset($ann[$k]);}
    saveList($ann,"my/$CORPUSLANG/tranko/$corpus/annotations_terms.ann");
}
