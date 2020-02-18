<?php

require_once "common.php";

function resetStats(){
    global $words,$wordsLower,$wordsFile,$wordsFileLower;
    global $globalGrams2,$globalGrams2File;
    global $globalGrams3,$globalGrams3File;
    global $globalGrams4,$globalGrams4File;
    global $globalGrams5,$globalGrams5File;
    global $letters;
    global $corpusWordsLower,$CORPUSLANG;

    $words=[];
    $wordsLower=[];
    $corpusWordsLower=[];
    $wordsFile=[];
    $wordsFileLower=[];

    $globalGrams2=[];
    $globalGrams2File=[];

    $globalGrams3=[];
    $globalGrams3File=[];

    $globalGrams4=[];
    $globalGrams4File=[];

    $globalGrams5=[];
    $globalGrams5File=[];

    $letters=loadStat("my/$CORPUSLANG/ann_letters.txt");
    for($i=ord('a');$i<=ord('z');$i++)$letters[chr($i)]=1;
    for($i=ord('A');$i<=ord('Z');$i++)$letters[chr($i)]=1;
    for($i=ord('0');$i<=ord('9');$i++)$letters[chr($i)]=1;
}


function processFile($fname){
    echo "$fname\n";
    
    global $words,$wordsLower,$wordsFile,$wordsFileLower;
    global $globalGrams2,$globalGrams2File;
    global $globalGrams3,$globalGrams3File;
    global $globalGrams4,$globalGrams4File;
    global $globalGrams5,$globalGrams5File;
    global $letters;
    global $corpusWordsLower;
    
    
    foreach(explode("\n",file_get_contents($fname)) as $fline){


    $localWords=[];
    $localWordsLower=[];
    $localGrams2=[];
    $localGrams3=[];
    $localGrams4=[];
    $localGrams5=[];



	foreach(getSentences($fline) as $line){
	//$line=preg_replace("/[^-'a-zA-Z0-9]/"," ",$line);
	$l="";
	for($i=0;$i<mb_strlen($line);$i++){
	    $c=mb_substr($line,$i,1);
	    if(isset($letters[$c]))$l.=$c;
	    else $l.=" ";
	}
	$line=$l;
	$line=preg_replace("/[ ]+/"," ",$line);
	$line=trim($line);
	$grams2=[];
	$grams3=[];
	$grams4=[];
	$grams5=[];
	foreach(getWords($line) as $word){
	    if(strlen($word)==0)continue;
	    
	    if(!isset($words[$word]))$words[$word]=0;
	    $words[$word]++;
	    
	    $wordLower=mb_strtolower($word);
	    if(!isset($wordsLower[$wordLower]))$wordsLower[$wordLower]=0;
	    $wordsLower[$wordLower]++;

	    if(!isset($corpusWordsLower[$wordLower]))$corpusWordsLower[$wordLower]=0;
	    $corpusWordsLower[$wordLower]++;
	    
	    if(!isset($localWords[$word]))$localWords[$word]=0;
	    $localWords[$word]++;

	    if(!isset($localWordsLower[$wordLower]))$localWordsLower[$wordLower]=0;
	    $localWordsLower[$wordLower]++;
	    
	    $grams2[]=$wordLower;
	    if(count($grams2)==2){
		$str=implode($grams2," ");
		
		if(!isset($globalGrams2[$str]))$globalGrams2[$str]=0;
		$globalGrams2[$str]++;
		
		if(!isset($localGrams2[$str]))$localGrams2[$str]=0;
		$localGrams2[$str]++;
		
		array_shift($grams2);
	    }


	    $grams3[]=$wordLower;
	    if(count($grams3)==3){
		$str=implode($grams3," ");
		
		if(!isset($globalGrams3[$str]))$globalGrams3[$str]=0;
		$globalGrams3[$str]++;
		
		if(!isset($localGrams3[$str]))$localGrams3[$str]=0;
		$localGrams3[$str]++;
		
		array_shift($grams3);
	    }

	    $grams4[]=$wordLower;
	    if(count($grams4)==4){
		$str=implode($grams4," ");
		
		if(!isset($globalGrams4[$str]))$globalGrams4[$str]=0;
		$globalGrams4[$str]++;
		
		if(!isset($localGrams4[$str]))$localGrams4[$str]=0;
		$localGrams4[$str]++;
		
		array_shift($grams4);
	    }

	    $grams5[]=$wordLower;
	    if(count($grams5)==5){
		$str=implode($grams5," ");
		
		if(!isset($globalGrams5[$str]))$globalGrams5[$str]=0;
		$globalGrams5[$str]++;
		
		if(!isset($localGrams5[$str]))$localGrams5[$str]=0;
		$localGrams5[$str]++;
		
		array_shift($grams5);
	    }

	}
    }




    foreach($localWords as $w=>$v){
	if(!isset($wordsFile[$w]))$wordsFile[$w]=0;
	$wordsFile[$w]++;
    }
    
    foreach($localWordsLower as $w=>$v){
	if(!isset($wordsFileLower[$w]))$wordsFileLower[$w]=0;
	$wordsFileLower[$w]++;
    }
    
    foreach($localGrams2 as $w=>$v){
	if(!isset($globalGrams2File[$w]))$globalGrams2File[$w]=0;
	$globalGrams2File[$w]++;
    }

    foreach($localGrams3 as $w=>$v){
	if(!isset($globalGrams3File[$w]))$globalGrams3File[$w]=0;
	$globalGrams3File[$w]++;
    }

    foreach($localGrams4 as $w=>$v){
	if(!isset($globalGrams4File[$w]))$globalGrams4File[$w]=0;
	$globalGrams4File[$w]++;
    }

    foreach($localGrams5 as $w=>$v){
	if(!isset($globalGrams5File[$w]))$globalGrams5File[$w]=0;
	$globalGrams5File[$w]++;
    }






    }
    

}

@mkdir("my");
@mkdir("my/$CORPUSLANG/tfidf");

foreach(["equi","corp","wind","hf"] as $corpus){
@mkdir("my/$CORPUSLANG/tfidf/$corpus");

//processFile("ACTER_version1_0_only_train/en/equi/texts/annotated/equi_en_001.txt");
resetStats();
$stoplist1=loadStat("my/$CORPUSLANG/stoplist.txt");
$stoplist2=loadStat("my/$CORPUSLANG/stoplist2.txt");
$stoplist=array_merge($stoplist1,$stoplist2);

$num=processFolder("$CORPUSBASE/$CORPUSLANG/$corpus/texts/annotated/");
//$num+=processFolder("ACTER_version1_0_only_train/en/equi/texts/unannotated/");
$tfidf=[];
foreach($wordsLower as $w=>$n){
    if(isset($stoplist[$w]))continue;
    $tfidf[$w]=log($n * $wordsFileLower[$w],2);
}
arsort($tfidf);
saveStat($tfidf,"my/$CORPUSLANG/${corpus}_1_tfidf.txt");

foreach($tfidf as $w=>$n){if($n<4)unset($tfidf[$w]);}


$all=$tfidf;
$tfidf=[];
foreach($globalGrams2 as $w=>$n){
    if(isset($stoplist[$w]))continue;
    $tfidf[$w]=log($n * $globalGrams2File[$w],2);
}
arsort($tfidf);
saveStat($tfidf,"my/$CORPUSLANG/${corpus}_2_tfidf.txt");

foreach($tfidf as $w=>$n){if($n<4)unset($tfidf[$w]);}
$all=array_merge($all,$tfidf);


$tfidf=[];
foreach($globalGrams3 as $w=>$n){
    if(isset($stoplist[$w]))continue;
    $tfidf[$w]=log($n * $globalGrams3File[$w],2);
}
arsort($tfidf);
saveStat($tfidf,"my/$CORPUSLANG/${corpus}_3_tfidf.txt");

foreach($tfidf as $w=>$n){if($n<4)unset($tfidf[$w]);}
$all=array_merge($all,$tfidf);


$tfidf=[];
foreach($globalGrams4 as $w=>$n){
    if(isset($stoplist[$w]))continue;
    $tfidf[$w]=log($n * $globalGrams4File[$w],2);
}
arsort($tfidf);
saveStat($tfidf,"my/$CORPUSLANG/${corpus}_4_tfidf.txt");

foreach($tfidf as $w=>$n){if($n<4)unset($tfidf[$w]);}
$all=array_merge($all,$tfidf);


saveList($all,"my/$CORPUSLANG/tfidf/${corpus}/annotations_terms.ann");
}
