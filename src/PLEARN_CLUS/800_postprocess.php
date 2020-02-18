<?php

require_once "common.php";
require_once "common_conll.php";
require_once "common_filter.php";

$stoplist=array_flip(loadList("my/$CORPUSLANG/stop_words.txt"));

$num_max_files=intval(file_get_contents("my/$CORPUSLANG/num_max_files.txt"));

$letters=loadStat("my/$CORPUSLANG/ann_letters.txt");
for($i=ord('a');$i<=ord('z');$i++)$letters[chr($i)]=1;
for($i=ord('A');$i<=ord('Z');$i++)$letters[chr($i)]=1;
for($i=ord('0');$i<=ord('9');$i++)$letters[chr($i)]=1;

unset($letters[',']);
unset($letters['.']);

$terms=[];

function processFileAnnotate($fname){
    global $stoplist,$terms,$ann,$letters;
    
    foreach(conll_getSentences(file_get_contents($fname)) as $sent){
	$words=conll_getWordsLower($sent);
	//$words=array_diff($words,array_keys($stoplist));
	//foreach($words as $k=>$w)if(mb_strlen($w)<2)unset($words[$k]);
	//$words=array_values($words);
	for($wordIndex=0;$wordIndex<count($words);$wordIndex++){
	    $word=$words[$wordIndex];
	    if(strlen($word)==0 || !isset($terms[$word]))continue;
	    
	    for($last=$wordIndex;(isset($terms[$words[$last]]) || isset($stoplist[$words[$last]]))&& $last<count($words)-1;$last++){
		$f=false;
		for($i=0;$i<mb_strlen($words[$last]);$i++){
		    if(!isset($letters[mb_substr($words[$last],$i,1)])){$f=true;break;}
		}
		if($f)break;
	    }
	    for(;$last>$wordIndex && !isset($terms[$words[$last]]);$last--);
	    
	    if($last-$wordIndex+1<5){
		$t=implode(" ",array_slice($words,$wordIndex,$last-$wordIndex+1));
		$ann[$t]=true;
	    }
	    
	    $wordIndex=$last;
	    
	}
    }
}

foreach(["equi","corp","wind","hf"] as $corpus){
foreach(["c_oanc"] as $method){

    echo "Postprocess $corpus => $method\n";

    $c=0;
    $terms=array_flip(loadList("my/$CORPUSLANG/$method/$corpus/annotations_terms.ann"));
    $ann=[];
    $num=processFolder("my/$CORPUSLANG/corenlp/$corpus",".conll","processFileAnnotate");
    foreach($ann as $a=>$v){
	if(!allowCandidate(explode(" ",$a),[],[]))unset($ann[$a]);
    }
    @mkdir("my/$CORPUSLANG/pp_${method}/");
    @mkdir("my/$CORPUSLANG/pp_${method}/$corpus");
    saveList($ann,"my/$CORPUSLANG/pp_${method}/$corpus/annotations_terms.ann");

}
}