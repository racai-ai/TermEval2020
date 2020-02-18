<?php

require "common.php";

$ann=[];

function processFile($fname){
    global $ann;

    echo "$fname\n";
    shell_exec("python RAKE_mod/rake.py RAKE/SmartStoplist.txt $fname > tmp.ro.txt");
    
    $data=loadStat("tmp.ro.txt");
    foreach($data as $k=>$v){
	if(!isset($ann[$k]))$ann[$k]=$v;
	else $ann[$k]+=$v;
    }
    @unlink("tmp.ro.txt");
}

@mkdir("my");
@mkdir("my/$CORPUSLANG/rakeo");

foreach(["equi","corp","wind","hf"] as $corpus){
    @mkdir("my/$CORPUSLANG/rakeo/$corpus");
    $ann=[];
    $num=processFolder("$CORPUSBASE/$CORPUSLANG/$corpus/texts/annotated/");
    arsort($ann);
    foreach($ann as $k=>$v){if($v<1)unset($ann[$k]);}
    saveList($ann,"my/$CORPUSLANG/rakeo/$corpus/annotations_terms.ann");
}
