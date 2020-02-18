<?php

require_once "common.php";

$stoplist=loadStat("my/$CORPUSLANG/stoplist.txt");

$terms=[];
function loadTerms($fname){
    global $terms;
    $terms=loadList($fname);
}

function processFileToConll($fname){
    global $ann,$stoplist;
    $first=true;
    foreach(explode("\n",file_get_contents($fname)) as $fline){
	foreach(getSentences($fline) as $line){
	    
	    if(!$first)file_put_contents("my/$CORPUSLANG/corpus_train.conllu","\n",FILE_APPEND);
	    else $first=false;
	    
	    $words=getWords($line);
	    $id=0; 
	    for($wordIndex=0;$wordIndex<count($words);$wordIndex++){
	        $word=$words[$wordIndex];
		if(strlen($word)==0)continue;
		$id++;
		$wordl=mb_strtolower($word);
		
		$cann="O"; 
		if(isset($ann[$wordl]))$cann=$ann[$wordl];
		else if(!isset($stoplist[$wordl]) && mb_strtoupper(mb_substr($word,0,1))==mb_substr($word,0,1))$cann="NNP";
		else if(isset($stoplist[$wordl]))$cann="IN";
		else $cann="VB";
		
		file_put_contents("my/$CORPUSLANG/corpus_train.conllu","$id\t$word\t_\t$cann\t$cann\t_\t0\t_\t_\t_\n",FILE_APPEND);
	    }
	}
    }
}

file_put_contents("my/$CORPUSLANG/corpus_train.conllu","");

$terms=[];
processFolder("$CORPUSBASE/$CORPUSLANG/corp/annotations/","_terms.ann","loadTerms");
$ann=[];
foreach($terms as $t){
    $words=getWords($t);
    if(count($words)==1){
	if(isset($stoplist[$t]))$ann[$t]="IN";
	else $ann[$t]="NN";
    }
}
$jj_candidates=[];
foreach($terms as $t){
    $words=getWords($t);
    for($i=0;$i<count($words)-1;$i++){
	if(!isset($stoplist[$words[$i]])){
	    if(!isset($jj_candidates[$words[$i]]))$jj_candidates[$words[$i]]=0;
	    $jj_candidates[$words[$i]]++;
	}
    }
}

foreach($terms as $t){
    $words=getWords($t);
    if(count($words)<2)continue;
    for($i=0;$i<count($words)-1;$i++){
	if(isset($stoplist[$words[$i]])){
	    if(!isset($ann[$words[$i]]))$ann[$words[$i]]="IN";
	}else if(isset($jj_candidates[$words[$i]]) && $jj_candidates[$words[$i]]>1){
	    if(!isset($ann[$words[$i]]))$ann[$words[$i]]="JJ";
	}else{
	    if(!isset($ann[$words[$i]]))$ann[$words[$i]]="NN";
	}
    }
    $ann[$words[count($words)-1]]="NN";
}

var_dump($jj_candidates);

processFolder("$CORPUSBASE/$CORPUSLANG/corp/texts/annotated/",".txt","processFileToConll");
processFolder("$CORPUSBASE/$CORPUSLANG/corp/texts/unannotated/",".txt","processFileToConll");









$terms=[];
processFolder("$CORPUSBASE/$CORPUSLANG/wind/annotations/","_terms.ann","loadTerms");
$ann=[];
foreach($terms as $t){
    $words=getWords($t);
    if(count($words)==1){
	if(isset($stoplist[$t]))$ann[$t]="IN";
	else $ann[$t]="NN";
    }
}
/*$jj_candidates=[];
foreach($terms as $t){
    $words=getWords($t);
    for($i=0;$i<count($words)-1;$i++){
	if(!isset($stoplist[$words[$i]])){
	    if(!isset($jj_candidates[$words[$i]]))$jj_candidates[$words[$i]]=0;
	    $jj_candidates[$words[$i]]++;
	}
    }
}*/

foreach($terms as $t){
    $words=getWords($t);
    if(count($words)<2)continue;
    for($i=0;$i<count($words)-1;$i++){
	if(isset($stoplist[$words[$i]])){
	    if(!isset($ann[$words[$i]]))$ann[$words[$i]]="IN";
//	}else if(isset($jj_candidates[$words[$i]]) && $jj_candidates[$words[$i]]>1){
//	    $ann[$words[$i]]="JJ";
	}else{
	    $ann[$words[$i]]="NN";
	}
    }
    $ann[$words[count($words)-1]]="NN";
}

//var_dump($jj_candidates);

processFolder("$CORPUSBASE/$CORPUSLANG/wind/texts/annotated/",".txt","processFileToConll");
processFolder("$CORPUSBASE/$CORPUSLANG/wind/texts/unannotated/",".txt","processFileToConll");

