<?php

require_once "common.php";

$stoplist=loadStat("my/$CORPUSLANG/stoplist.txt");

$terms=[];
function loadTerms($fname){
    global $terms;
    $terms=loadList($fname);
}

function processFileToConll($fname){
    global $ann,$stoplist,$CORPUSLANG;
    $first=true;
    foreach(explode("\n",file_get_contents($fname)) as $fline){
	foreach(getSentences($fline) as $line){
	    
	    if(!$first)file_put_contents("my/$CORPUSLANG/corpus_train_nn.conllu","\n",FILE_APPEND);
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
		
		file_put_contents("my/$CORPUSLANG/corpus_train_nn.conllu","$id\t$word\t_\t$cann\t$cann\t_\t0\t_\t_\t_\n",FILE_APPEND);
	    }
	}
    }
}

file_put_contents("my/$CORPUSLANG/corpus_train_nn.conllu","");

foreach(["equi","corp","wind"] as $corpus){
$terms=[];
processFolder("$CORPUSBASE/$CORPUSLANG/$corpus/annotations/","_terms.ann","loadTerms");
$ann=[];
foreach($terms as $t){
    $t=mb_strtolower($t);
    $words=getWords($t);
    if(count($words)==1 && !isset($stoplist[$words[0]]) && strlen($words[0])>3){
	$ann[$words[0]]="NN";
	echo "1 ===> ${words[0]}\n";
    }
}
foreach($terms as $t){
    $t=mb_strtolower($t);
    $words=getWords($t);
    if(count($words)<2)continue;
    if(!isset($stoplist[$words[count($words)-1]])){
	$ann[$words[count($words)-1]]="NN";
	echo "n ===> ".$words[count($words)-1]."\n";
    }
}

processFolder("$CORPUSBASE/$CORPUSLANG/corp/texts/annotated/",".txt","processFileToConll");
processFolder("$CORPUSBASE/$CORPUSLANG/corp/texts/unannotated/",".txt","processFileToConll");

}