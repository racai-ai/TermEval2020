<?php

require_once "common.php";

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

function processFile($fname){
    echo "$fname\n";

    global $words,$wordsLower,$wordsFile,$wordsFileLower;
    global $globalGrams2,$globalGrams2File;
    global $globalGrams3,$globalGrams3File;
    global $globalGrams4,$globalGrams4File;
    global $globalGrams5,$globalGrams5File;
    global $letters;
    global $corpusWordsLower;
    
    $localWords=[];
    $localWordsLower=[];
    $localGrams2=[];
    $localGrams3=[];
    $localGrams4=[];
    $localGrams5=[];
    
    foreach(explode("\n",file_get_contents($fname)) as $fline){
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


//processFile("ACTER_version1_0_only_train/en/equi/texts/annotated/equi_en_001.txt");
$corpusWordsLower=[];
$num=processFolder("$CORPUSBASE/$CORPUSLANG/equi/texts/annotated/");
$num+=processFolder("$CORPUSBASE/$CORPUSLANG/equi/texts/unannotated/");
$max=$num;
saveStat($corpusWordsLower,"my/$CORPUSLANG/equi_lower.txt");

$corpusWordsLower=[];
$num=processFolder("$CORPUSBASE/$CORPUSLANG/corp/texts/annotated/");
$num+=processFolder("$CORPUSBASE/$CORPUSLANG/corp/texts/unannotated/");
if($num>$max)$max=$num;
saveStat($corpusWordsLower,"my/$CORPUSLANG/corp_lower.txt");

$corpusWordsLower=[];
$num=processFolder("$CORPUSBASE/$CORPUSLANG/wind/texts/annotated/");
$num+=processFolder("$CORPUSBASE/$CORPUSLANG/wind/texts/unannotated/");
if($num>$max)$max=$num;
saveStat($corpusWordsLower,"my/$CORPUSLANG/wind_lower.txt");

$corpusWordsLower=[];
$num=processFolder("$CORPUSBASE/$CORPUSLANG/hf/texts/annotated/");
if($num>$max)$max=$num;
saveStat($corpusWordsLower,"my/$CORPUSLANG/hf_lower.txt");

file_put_contents("my/$CORPUSLANG/num_max_files.txt","$max");

saveStat($words,"my/$CORPUSLANG/stats_1.txt");
saveStat($wordsLower,"my/$CORPUSLANG/stats_1_lower.txt");
saveStat($wordsFile,"my/$CORPUSLANG/stats_1_file.txt");
saveStat($wordsFileLower,"my/$CORPUSLANG/stats_1_file_lower.txt");
saveStat($globalGrams2,"my/$CORPUSLANG/stats_2_lower.txt");
saveStat($globalGrams2File,"my/$CORPUSLANG/stats_2_file_lower.txt");
saveStat($globalGrams3,"my/$CORPUSLANG/stats_3_lower.txt");
saveStat($globalGrams3File,"my/$CORPUSLANG/stats_3_file_lower.txt");
saveStat($globalGrams4,"my/$CORPUSLANG/stats_4_lower.txt");
saveStat($globalGrams4File,"my/$CORPUSLANG/stats_4_file_lower.txt");
saveStat($globalGrams5,"my/$CORPUSLANG/stats_5_lower.txt");
saveStat($globalGrams5File,"my/$CORPUSLANG/stats_5_file_lower.txt");


$words=[];
$wordsLower=[];
$wordsFile=[];
$wordsFileLower=[];

$globalGrams2=[];
$globalGrams2File=[];

$globalGrams3=[];
$globalGrams3File=[];

$globalGrams4=[];
$globalGrams4File=[];

processFolder("$CORPUSBASE/$CORPUSLANG/equi/annotations/","_terms.ann");
processFolder("$CORPUSBASE/$CORPUSLANG/corp/annotations/","_terms.ann");
processFolder("$CORPUSBASE/$CORPUSLANG/wind/annotations/","_terms.ann");
saveStat($words,"my/$CORPUSLANG/ann_1.txt");
saveStat($wordsLower,"my/$CORPUSLANG/ann_1_lower.txt");
saveStat($wordsFile,"my/$CORPUSLANG/ann_1_file.txt");
saveStat($wordsFileLower,"my/$CORPUSLANG/ann_1_file_lower.txt");
saveStat($globalGrams2,"my/$CORPUSLANG/ann_2_lower.txt");
saveStat($globalGrams2File,"my/$CORPUSLANG/ann_2_file_lower.txt");
saveStat($globalGrams3,"my/$CORPUSLANG/ann_3_lower.txt");
saveStat($globalGrams3File,"my/$CORPUSLANG/ann_3_file_lower.txt");
saveStat($globalGrams4,"my/$CORPUSLANG/ann_4_lower.txt");
saveStat($globalGrams4File,"my/$CORPUSLANG/ann_4_file_lower.txt");
