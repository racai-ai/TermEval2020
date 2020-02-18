<?php

require_once "common.php";

function processFileToConll($fname){
    $first=true;
    foreach(explode("\n",file_get_contents($fname)) as $fline){
	foreach(getSentences($fline) as $line){
	    
	    if(!$first)file_put_contents("my/$CORPUSLANG/corpus_test.conllu","\n",FILE_APPEND);
	    else $first=false;
	    
	    $words=getWords($line);
	    $id=0; 
	    for($wordIndex=0;$wordIndex<count($words);$wordIndex++){
	        $word=$words[$wordIndex];
		if(strlen($word)==0)continue;
		$id++;
		
		file_put_contents("my/$CORPUSLANG/corpus_train.conllu","$id\t$word\t_\t_\t_\t_\t0\t_\t_\t_\n",FILE_APPEND);
	    }
	}
    }
}

file_put_contents("my/$CORPUSLANG/corpus_test.conllu","");


processFolder("$CORPUSBASE/$CORPUSLANG/equi/texts/annotated/",".txt","processFileToConll");
