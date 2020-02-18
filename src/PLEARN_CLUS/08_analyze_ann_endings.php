<?php

require_once "common.php";

loadRootsEndings();

$termEndings_start=[];
$termEndings_end=[];
$termEndings_contains=[];

function processFile($fname){
    global $roots,$roots_r,$endings,$termEndings_start,$termEndings_end,$termEndings_contains;
    
    foreach(explode("\n",file_get_contents($fname)) as $line){
	$line=trim($line);
	if(strlen($line)==0)continue;
	
	$words=explode(" ",$line);
	
	if(count($words)<2)continue;
	
	for($i=0;$i<count($words);$i++){
	    if(isset($roots[$words[$i]])){
		$elen=strlen($words[$i])-strlen($roots[$words[$i]]);
		$ending=substr($words[$i],strlen($words[$i])-$elen);
	    
		if($i==0){
		    if(!isset($termEndings_start[$ending]))$termEndings_start[$ending]=0;
		    $termEndings_start[$ending]++;
		}else if($i==count($words)-1){
		    if(!isset($termEndings_end[$ending]))$termEndings_end[$ending]=0;
		    $termEndings_end[$ending]++;
		}else{
		    if(!isset($termEndings_contains[$ending]))$termEndings_contains[$ending]=0;
		    $termEndings_contains[$ending]++;
		}
	    }
	}

    }

}


processFolder("$CORPUSBASE/$CORPUSLANG/equi/annotations/","_terms.ann");
processFolder("$CORPUSBASE/$CORPUSLANG/corp/annotations/","_terms.ann");
processFolder("$CORPUSBASE/$CORPUSLANG/wind/annotations/","_terms.ann");
saveStat($termEndings_start,"my/$CORPUSLANG/ann_endings_start.txt");
saveStat($termEndings_contains,"my/$CORPUSLANG/ann_endings_contains.txt");
saveStat($termEndings_end,"my/$CORPUSLANG/ann_endings_end.txt");
