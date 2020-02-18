<?php

require_once "common.php";

function filter(&$vec,$n){
    foreach($vec as $k=>$v){
    
	if(count(explode(" ",$v))!=$n)unset($vec[$k]);
    
    }
}


function processPair($fn1,$fn2,$n=false,$corpus=false,$method=false){
    global $CORPUSLANG;

    $l1=loadList($fn1);
    $l2=loadList($fn2);
    
    if($n!==false){
	filter($l1,$n);
	filter($l2,$n);
    }
    

    $TP=floatval(count(array_intersect($l1,$l2)));
    $FP=floatval(count(array_diff($l1,$l2)));
    $FN=floatval(count(array_diff($l2,$l1)));
    $PRECISION=0; if($TP>0)$PRECISION=($TP/($TP+$FP))*100.0;
    $RECALL=0; if($TP>0)$RECALL=($TP/($TP+$FN))*100.0;
    $F1=0; 
    if($PRECISION>0 && $RECALL>0){
	$F1=2*$PRECISION*$RECALL/($PRECISION+$RECALL);
    }
    
    echo "$corpus\t$method\t".(($n===false)?("all"):($n)).
	"\tTP=".round($TP,2)."\tFP=".round($FP,2)."\tFN=".round($FN,2).
	"\tP=".round($PRECISION,2)."\tR=".round($RECALL,2)."\tF1=".round($F1,2)."\n";
    
    if($corpus!==false){
	$fname="my/$CORPUSLANG/$corpus.$method.".(($n===false)?("all"):($n)).".FP.diff";
	//echo "saving $fname\n";
	$diff=array_flip(array_diff($l1,$l2));
	saveList($diff,$fname);

	$fname="my/$CORPUSLANG/$corpus.$method.".(($n===false)?("all"):($n)).".FN.diff";
	//echo "saving $fname\n";
	$diff=array_flip(array_diff($l2,$l1));
	saveList($diff,$fname);
    }
}

$folders=array_merge([
    "tfidf","tfidff","rakeo","rake","yakeo","yake","trank","tranko",
    "mytr","my","kl",
    //"nn1","nn2","nn3","nn4","dt",
    "c_my","c_crawl","c_oanc","c_lemma",
    "pp_c_oanc",
    "radu"
],array_keys($combinations)) ;
if($argc>1)$folders=[$argv[1]];

echo "\n\n\nEvaluation results:\n";

foreach(["equi","corp","wind","hf"] as $corpus){
    foreach($folders as $method){
	foreach([1,2,3,4,false] as $N){
	    processPair("my/$CORPUSLANG/$method/$corpus/annotations_terms.ann","$CORPUSBASE/$CORPUSLANG/$corpus/annotations/${corpus}_${CORPUSLANG}_terms_nes.ann",$N,$corpus,$method);
	}
    }
    echo "\n";
}
