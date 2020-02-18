<?php

require "common.php";

$stat1=loadStat("my/${CORPUSLANG}/stats_1_lower.txt");
$stoplist=array_flip(loadList("my/${CORPUSLANG}/stop_words.txt"));

$totalTokens=0;
foreach($stat1 as $k=>$v)$totalTokens+=$v;

@mkdir("my/${CORPUSLANG}/kl");

foreach(["equi","wind","corp","hf"] as $c){
    @mkdir("my/${CORPUSLANG}/kl/$c");
    $weights=[];
    $localStat=loadStat("my/${CORPUSLANG}/${c}_lower.txt");
    var_dump(count($localStat));
    $localTotal=0;
    foreach($localStat as $k=>$v)$localTotal+=$v;
    foreach($localStat as $t=>$f){
	$PX=(floatval($f)/floatval($localTotal));
	$PC=(floatval($stat1[$t])/floatval($totalTokens));
	$weights[$t]= $PX * log($PX/$PC,2);
    }
    
    $path="my/${CORPUSLANG}/kl_${c}_stat.txt";
    var_dump($path);
    saveStat($weights,$path);
    
    $cnt=0;
    $pred=[];
    foreach($weights as $w=>$v){
	if(!isset($stoplist[$w])){
	    $pred[$w]=$v;
	    $cnt++;
	    if($cnt>count($weights)/100)break;
	}
    }
	    
    
    saveList($pred,"my/${CORPUSLANG}/kl/$c/annotations_terms.ann");
}

