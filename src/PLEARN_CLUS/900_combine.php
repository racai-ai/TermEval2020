<?php

require_once "common.php";

require_once "common_filter.php";

$final=[];

function combine($corpus,$method){
    global $final,$CORPUSLANG;
    
    if(startsWith($method,"+")){$method=substr($method,1);}
    
    $numTokens=0;
    if(startsWith($method,"1:")){$numTokens=1; $method=substr($method,2);}
    if(startsWith($method,"2:")){$numTokens=2; $method=substr($method,2);}
    if(startsWith($method,"3:")){$numTokens=3; $method=substr($method,2);}
    if(startsWith($method,"4:")){$numTokens=4; $method=substr($method,2);}
    
    $ann=loadList("my/$CORPUSLANG/$method/$corpus/annotations_terms.ann");
    foreach($ann as $a){
	if($numTokens>0){
	    $c=substr_count($a," ");
	    if($c!=$numTokens-1)continue;
	}
	if(!isset($final[$a]))$final[$a]=0;
	$final[$a]++;
    }
}

function filter($m){
    global $final;

    foreach($final as $f=>$v)
	if($v<$m || !allowCandidate(explode(" ",$f),[],[]))unset($final[$f]);
}



foreach($combinations as $comb=>$methods){
    @mkdir("my/$CORPUSLANG/$comb");
    foreach(["equi","corp","wind","hf"] as $corpus){
	$final=[];
	foreach($methods as $method){
	    if(!startsWith($method,"+"))
		combine($corpus,$method);
	}
	filter(2);
	foreach($methods as $method){
	    if(startsWith($method,"+"))
		combine($corpus,substr($method,1));
	}
	

	@mkdir("my/$CORPUSLANG/$comb/$corpus");
	saveList($final,"my/$CORPUSLANG/$comb/$corpus/annotations_terms.ann");
    }
}
