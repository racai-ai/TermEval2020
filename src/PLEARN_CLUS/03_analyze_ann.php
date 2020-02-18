<?php

require_once "common.php";

$start=[];
$end=[];
$contains=[];
$contains_2g=[];
$counts=[];


$start_1=[];
$start_2=[];
$end_2=[];
$start_3p=[];
$end_3p=[];

$letters=loadStat("my/$CORPUSLANG/ann_letters.txt");
for($i=ord('a');$i<=ord('z');$i++)$letters[chr($i)]=1;
for($i=ord('A');$i<=ord('Z');$i++)$letters[chr($i)]=1;
for($i=ord('0');$i<=ord('9');$i++)$letters[chr($i)]=1;

function processFile($fname){
    echo "$fname\n";
    
    global $start,$end,$contains,$counts;
    global $start_1,$start_2,$start_3p;
    global $end_2,$end_3p,$contains_2g;
    
    foreach(explode("\n",file_get_contents($fname)) as $line){
	$line=trim($line);
	if(strlen($line)==0)continue;
	
	$words=explode(" ",$line);
	if(!isset($counts[count($words)]))$counts[count($words)]=0;
	$counts[count($words)]++;
	
	if(!isset($start[$words[0]]))$start[$words[0]]=0;
	$start[$words[0]]++;
	
	if(count($words)>1){
	    if(!isset($end[$words[count($words)-1]]))$end[$words[count($words)-1]]=0;
	    $end[$words[count($words)-1]]++;
	}
	
	for($i=1;$i<count($words)-1;$i++){
	    if(!isset($contains[$words[$i]]))$contains[$words[$i]]=0;
	    $contains[$words[$i]]++;
	}
	
	if(count($words)==1){
	    if(!isset($start_1[$words[0]]))$start_1[$words[0]]=0;
	    $start_1[$words[0]]++;
	}

	if(count($words)==2){
	    if(!isset($start_2[$words[0]]))$start_2[$words[0]]=0;
	    $start_2[$words[0]]++;

	    if(!isset($end_2[$words[1]]))$end_2[$words[1]]=0;
	    $end_2[$words[1]]++;
	}

	if(count($words)>=3){
	    if(!isset($start_3p[$words[0]]))$start_3p[$words[0]]=0;
	    $start_3p[$words[0]]++;

	    if(!isset($end_3p[$words[count($words)-1]]))$end_3p[$words[count($words)-1]]=0;
	    $end_3p[$words[count($words)-1]]++;
	    
	    for($i=1;$i<count($words)-2;$i++){
		$g=implode(" ",array_slice($words,$i,2));
		if(!isset($contains_2g[$g]))$contains_2g[$g]=0;
		$contains_2g[$g]++;
	    }
	}

    }

}


processFolder("$CORPUSBASE/$CORPUSLANG/equi/annotations/","_terms.ann");
processFolder("$CORPUSBASE/$CORPUSLANG/corp/annotations/","_terms.ann");
processFolder("$CORPUSBASE/$CORPUSLANG/wind/annotations/","_terms.ann");
saveStat($start,"my/$CORPUSLANG/ann_start.txt");
saveStat($contains,"my/$CORPUSLANG/ann_contains.txt");
saveStat($end,"my/$CORPUSLANG/ann_end.txt");
saveStat($counts,"my/$CORPUSLANG/ann_counts.txt");
saveStat($start_1,"my/$CORPUSLANG/ann_start_1.txt");
saveStat($start_2,"my/$CORPUSLANG/ann_start_2.txt");
saveStat($end_2,"my/$CORPUSLANG/ann_end_2.txt");
saveStat($start_3p,"my/$CORPUSLANG/ann_start_3p.txt");
saveStat($end_3p,"my/$CORPUSLANG/ann_end_3p.txt");
saveStat($contains_2g,"my/$CORPUSLANG/ann_contains_2g.txt");
