<?php

function conll_getSentences($fdata){

    $sentences=[];

    $sent=[];

    foreach(explode("\n",$fdata) as $line){
	$line=trim($line);
	
	if(strlen($line)==0){
	    if(count($sent)>0)$sentences[]=$sent;
	    $sent=[];
	    continue;
	}
	
	if($line[0]=='#')continue;
	
	$data=explode("\t",$line);
	if(count($data)<4)continue;
	$sent[]=["word"=>$data[1],"lemma"=>$data[2],"pos"=>$data[3]];
    }
    
    if(count($sent)>0)$sentences[]=$sent;
    
    return $sentences;

}

function conll_getWordsLower($sent){
    $words=[];
    foreach($sent as $tok)$words[]=mb_strtolower($tok['word']);
    return $words;
}

function conll_getLemmaLower($sent){
    $words=[];
    foreach($sent as $tok)$words[]=mb_strtolower($tok['lemma']);
    return $words;
}

