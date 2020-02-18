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

$graph=[];

function addLinkTo($w1,$word){
    global $graph,$roots;
    
    if(!isset($graph[$word])){
	$graph[$word]=['in'=>[],'out'=>[],'s'=>1.0];
    }

    if(!isset($graph[$w1])){
	$graph[$w1]=['in'=>[],'out'=>[],'s'=>1.0];
    }

    if(!isset($graph[$word]['in'][$w1])){
	$graph[$word]['in'][$w1]=true;
    }
    if(!isset($graph[$word]['out'][$w1])){
	$graph[$word]['out'][$w1]=true;
    }
    
    if(!isset($graph[$w1]['in'][$word])){
	$graph[$w1]['in'][$word]=true;
    }

    if(!isset($graph[$w1]['out'][$word])){
	$graph[$w1]['out'][$word]=true;
    }
    
}

function processCandidate($word,$next,$before){
    global $graph,$roots;
    
    for($i=count($before)-1;$i>=count($before)-2 && $i>=0;$i--){
	addLinkTo($before[$i]['word'],$word['word']);

	addLinkTo($before[$i]['lemma'],$word['word']);
	addLinkTo($before[$i]['word'],$word['lemma']);
	addLinkTo($before[$i]['lemma'],$word['lemma']);
	addLinkTo($word['lemma'],$word['word']);
    }
	
    for($i=0;$i<2 && $i<count($next);$i++){
	addLinkTo($next[$i]['word'],$word['word']);

	addLinkTo($next[$i]['lemma'],$word['word']);
	addLinkTo($next[$i]['word'],$word['lemma']);
	addLinkTo($next[$i]['lemma'],$word['lemma']);
	addLinkTo($word['lemma'],$word['word']);
    }
}

function processFile($fname){
    echo "$fname\n";
    global $graph,$stoplist;
    $allowedTags=array_flip(["NN","JJ","RB","CD","RP"]);//,"VBG","VBN"]);

    foreach(conll_getSentences(file_get_contents($fname)) as $sent){
	$words=$sent;
	//$words=conll_getWordsLower($sent);
	//$words=array_diff($words,array_keys($stoplist));
	foreach($words as $k=>$w){
	    $w['word']=mb_strtolower($w['word']);
	    $words[$k]=$w;
	    if(
//		isset($stoplist[$w['word']])
		//|| 
		mb_strlen($w['word'])<2 
//		|| (!isset($allowedTags[$w['pos']]) && !isset($allowedTags[substr($w['pos'],0,2)]))
	    ){
		unset($words[$k]);
	    }
	}
	$words=array_values($words);
	for($wordIndex=0;$wordIndex<count($words);$wordIndex++){
	    $word=$words[$wordIndex];
	    
	    $nextWords=array_slice($words,$wordIndex+1,5);
	    $beforeWords=[];
	    
	    $num=min(5,$wordIndex);
	    if($num>0)$beforeWords=array_slice($words,$wordIndex-$num,$num);
	    else $beforeWords=[];
	    processCandidate($word,$nextWords,$beforeWords);
	    
	}
    }
    
//    var_dump($graph);die();

}

function updateScore(){
    global $graph;
    
    $D=0.85;
    
    $graph1=[];
    $maxDiff=0;
    foreach($graph as $word=>$node){
	$s=0.0;
	foreach($node['in'] as $n=>$t){
	    $s+=floatval($graph[$n]['s'])/floatval(count($graph[$n]['out']));
	}
	$graph1[$word]['s']=(1-$D)+$D*$s;
	
	$diff=abs($graph1[$word]['s']-$node['s']);
	if($diff>$maxDiff)$maxDiff=$diff;
    }
    
    foreach($graph1 as $word=>$node){
	$graph[$word]['s']=$node['s'];
    }
    
    return $maxDiff;
}

function sortGraph($n1,$n2){
    return $n2['s']-$n1['s'];
}

function processFileAnnotate($fname){
    global $graph,$stoplist,$terms,$ann,$letters;
    
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

function filterKCore($k){
    global $graph;
    
    $removed=1;
    $totalRemoved=0;
    while($removed>0){
	$removed=0;
	
	foreach($graph as $w=>$v){
	    if(count($v['out'])<$k){
		$removed++;
		unset($graph[$w]);
		foreach($graph as $w1=>$v1){
		    if(isset($v1['out'][$w]))unset($graph[$w1]['out'][$w]);
		    if(isset($v1['in'][$w]))unset($graph[$w1]['in'][$w]);
		}
	    }
	}
	
	$totalRemoved+=$removed;
    }
    
    echo "FilterKCore $k removed $totalRemoved nodes\n";
}

@mkdir("my");
@mkdir("my/$CORPUSLANG/mytr");

foreach(["equi","corp","wind"] as $corpus){
    @mkdir("my/$CORPUSLANG/mytr/$corpus");

    $graph=[];
    $num=processFolder("my/$CORPUSLANG/corenlp/$corpus",".conll");
    
    filterKCore(4);
    
    for($i=0;$i<100;$i++){
	echo "Iter $i ... ";
	$diff=updateScore();
	echo "$diff\n";
	if($diff<0.0001)break;
    }
    
    uasort($graph,"sortGraph");
    
    $c=0;
    $terms=[];
    $ann=[];
    foreach($graph as $word=>$node){
	$c++;
	//echo "$word => ".$node['s']."\n";
	$terms[$word]=true;
	//if($c>600)break;
	if($c>1200)break;
    }

    $num=processFolder("my/$CORPUSLANG/corenlp/$corpus",".conll","processFileAnnotate");
    foreach($ann as $a=>$v){
	if(!allowCandidate(explode(" ",$a),[],[]))unset($ann[$a]);
    }
    saveList($ann,"my/$CORPUSLANG/mytr/$corpus/annotations_terms.ann");

}
