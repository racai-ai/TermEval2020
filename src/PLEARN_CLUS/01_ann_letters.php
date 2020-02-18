<?php

require "common.php";

$letters=[];

function processFile($fname){
    global $letters;
    
    foreach(explode("\n",file_get_contents($fname)) as $fline){
	for($i=0;$i<mb_strlen($fline);$i++){
	    $l=mb_substr($fline,$i,1);
	    if($l==' ')continue;
	    if(!isset($letters[$l]))$letters[$l]=1;
	    else $letters[$l]++;
	}
    }

}


processFolder("$CORPUSBASE/$CORPUSLANG/equi/annotations/","_terms.ann");
processFolder("$CORPUSBASE/$CORPUSLANG/corp/annotations/","_terms.ann");
processFolder("$CORPUSBASE/$CORPUSLANG/wind/annotations/","_terms.ann");
saveStat($letters,"my/$CORPUSLANG/ann_letters.txt");
