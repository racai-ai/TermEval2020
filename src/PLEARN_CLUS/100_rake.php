<?php

require "common.php";

$ann=[];

function processFile($fname){
    global $ann,$CORPUSLANG;

    echo "$fname\n";
    shell_exec("python RAKE_mod/rake.py my/$CORPUSLANG/stop_words.txt $fname > tmp.r.txt");
    
    $data=loadStat("tmp.r.txt");
    foreach($data as $k=>$v){
	if(!isset($ann[$k]))$ann[$k]=$v;
	else $ann[$k]+=$v;
    }
    @unlink("tmp.r.txt");
}

@mkdir("my");
@mkdir("my/$CORPUSLANG/rake");

foreach(["equi","corp","wind","hf"] as $corpus){
    @mkdir("my/$CORPUSLANG/rake/$corpus");
    $ann=[];
    $num=processFolder("$CORPUSBASE/$CORPUSLANG/$corpus/texts/annotated/");
    arsort($ann);
    foreach($ann as $k=>$v){if($v<1)unset($ann[$k]);}
    saveList($ann,"my/$CORPUSLANG/rake/$corpus/annotations_terms.ann");
}
