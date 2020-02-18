<?php

require_once "common.php";

$stats1=loadStat("my/$CORPUSLANG/stats_1_lower.txt");

foreach($stats1 as $word=>$v){
     if(strlen($word)<3)unset($stats1[$word]);
}


$stats1k=array_keys($stats1);

$roots=[];
$endings=[];

for($i=0;$i<count($stats1);$i++){
    echo "$i / ".count($stats1)."\n";
    $word=$stats1k[$i];
    if(is_numeric($word))continue;
    for($j=$i+1;$j<count($stats1);$j++){
	$word2=$stats1k[$j];

	if(is_numeric($word2))continue;

	$w1=$word;
	$w2=$word2;
	if(strlen($word2)<strlen($word)){
	    $w1=$word2;
	    $w2=$word;
	}
	
	// common start
	if(strncasecmp($w2,$w1,strlen($w1))==0){
	    $ending=substr($w2,strlen($w1));
	    if(strlen($ending)>3 || is_numeric($ending))continue;
	
	    $roots[$w2]=$w1;
	    if(!isset($endings[$ending]))$endings[$ending]=0;
	    $endings[$ending]++;
	}
    }
}

saveStat($roots,"my/$CORPUSLANG/roots.txt");
saveStat($endings,"my/$CORPUSLANG/endings.txt");

