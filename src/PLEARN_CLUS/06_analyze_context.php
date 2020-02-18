<?php

require_once "common.php";

$terms=[];
$after=[];
$before=[];

function processFile($fname){
    global $terms,$after,$before;
    
    echo "$fname\n";
    
    if(endsWith($fname,".ann")){
	$terms=loadList($fname);
	
	foreach($terms as $k=>$t){
	    $words=explode(" ",$t);
	    if(count($words)<3)unset($terms[$k]);
	}
	
	$pos=strrpos($fname,"/");
	$path=substr($fname,0,$pos);
	$path="$path/../texts/annotated";
	//var_dump($path);die();
	processFolder($path,".txt");
    }else{
	$data=file_get_contents($fname);
	foreach(explode("\n",$data) as $line){
	foreach($terms as $term){
	    $pos=strpos($line,$term);
	    if($pos!==false){
		$s=trim(substr($line,$pos+strlen($term),200));
		
		$sentences=getSentences($s);
		if(count($sentences)>0){
		    $words=getWords($sentences[0]);
		    $w=trim(mb_strtolower($words[0]));
		    if(!isset($after[$w]))$after[$w]=0;
		    $after[$w]++;
		}
		
		$s=trim(substr($line,max(0,$pos-200),$pos));
		$sentences=getSentences($s);
		if(count($sentences)>0){
		    $words=getWords($sentences[0]);
		    $w=trim(mb_strtolower($words[count($words)-1]));
		    if(!isset($before[$w]))$before[$w]=0;
		    $before[$w]++;
		}
	    }
	}
	}
	
    }
}

processFolder("$CORPUSBASE/$CORPUSLANG/equi/annotations/","_terms.ann");
processFolder("$CORPUSBASE/$CORPUSLANG/corp/annotations/","_terms.ann");
processFolder("$CORPUSBASE/$CORPUSLANG/wind/annotations/","_terms.ann");

saveStat($after,"my/$CORPUSLANG/after.txt");
saveStat($before,"my/$CORPUSLANG/before.txt");
