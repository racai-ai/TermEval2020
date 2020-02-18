<?php

require_once "common.php";

$start=loadStat("my/$CORPUSLANG/ann_start.txt");
$end=loadStat("my/$CORPUSLANG/ann_end.txt");
$contains=loadStat("my/$CORPUSLANG/ann_contains.txt");
$contains_2g=loadStat("my/$CORPUSLANG/ann_contains_2g.txt");
$start1=loadStat("my/$CORPUSLANG/ann_start_1.txt");
$start2=loadStat("my/$CORPUSLANG/ann_start_2.txt");
$start3p=loadStat("my/$CORPUSLANG/ann_start_3p.txt");
$end2=loadStat("my/$CORPUSLANG/ann_end_2.txt");
$end3p=loadStat("my/$CORPUSLANG/ann_end_3p.txt");

$stat1=loadStat("my/$CORPUSLANG/stats_1_lower.txt");
$stat2=loadStat("my/$CORPUSLANG/stats_2_lower.txt");
$stat3=loadStat("my/$CORPUSLANG/stats_3_lower.txt");
$stat4=loadStat("my/$CORPUSLANG/stats_4_lower.txt");

$stat1f=loadStat("my/$CORPUSLANG/stats_1_file_lower.txt");
$stat2f=loadStat("my/$CORPUSLANG/stats_2_file_lower.txt");
$stat3f=loadStat("my/$CORPUSLANG/stats_3_file_lower.txt");
$stat4f=loadStat("my/$CORPUSLANG/stats_4_file_lower.txt");

$afterTerm=loadStat("my/$CORPUSLANG/after.txt");
$beforeTerm=loadStat("my/$CORPUSLANG/before.txt");

$num_max_files=intval(file_get_contents("my/$CORPUSLANG/num_max_files.txt"));

$letters=loadStat("my/$CORPUSLANG/ann_letters.txt");
for($i=ord('a');$i<=ord('z');$i++)$letters[chr($i)]=1;
for($i=ord('A');$i<=ord('Z');$i++)$letters[chr($i)]=1;
for($i=ord('0');$i<=ord('9');$i++)$letters[chr($i)]=1;

unset($letters[',']);
unset($letters['.']);

function getStats($words){
    global $stat1,$stat2,$stat3,$stat4;
    
    $s=implode(" ",$words);
    if(count($words)==1)return $stat1[$s];
    if(count($words)==2)return $stat2[$s];
    if(count($words)==3)return $stat3[$s];
    if(count($words)==4)return $stat4[$s];
    return 0;
}

function getStatsf($words){
    global $stat1f,$stat2f,$stat3f,$stat4f;
    
    $s=implode(" ",$words);
    if(count($words)==1)return $stat1f[$s];
    if(count($words)==2)return $stat2f[$s];
    if(count($words)==3)return $stat3f[$s];
    if(count($words)==4)return $stat4f[$s];
    return 0;
}

function reject($rule,$words, $context=false){
    echo "[$rule] ".implode(" ",$words);
    if($context!==false)echo " [$context]";
    echo "\n";
    return false;
}

function allowCandidate($words,$next,$before){
    global $ann,$stoplist,$start,$end,$contains,$letters,$stoplist1,$stoplist2;
    global $start1,$start2,$start3p,$end2,$end3p;
    global $afterTerm,$beforeTerm;
    global $stat2f,$contains_2g;
    
    if(count($words)<1)return false; // currently only 2,3,4 grams
    
/*    foreach($words as $w){
	for($i=0;$i<mb_strlen($w);$i++){
	    $l=mb_substr($w,$i,1);
	    if(!isset($letters[$l]))return reject("letters",$words);
	}
    }
*/    
    if(isset($stoplist[$words[0]]) && !isset($start[$words[0]]))return reject("STOP1",$words);
    if(isset($stoplist[$words[count($words)-1]]) && !isset($end[$words[count($words)-1]]))return reject("STOP2",$words);

    if(count($words)>=3){
	if(isset($stoplist[$words[0]]) && !isset($start3p[$words[0]]))return reject("STOP3",$words);
	if(isset($stoplist[$words[count($words)-1]]) && !isset($end3p[$words[count($words)-1]]))return reject("STOP4",$words);
    }
    
    $found=false;
    for($i=1;$i<count($words)-1;$i++)
        if(isset($stoplist[$words[$i]]) && !isset($contains[$words[$i]])){$found=true;break;}
    if($found)return reject("STOP5",$words);
    
    for($i=0;$i<count($words);$i++)
        if(strlen($words[$i])>1){$found=true;break;}
    if(!$found)return reject("SMALL",$words);

    $found=false;
    for($i=0;$i<count($words);$i++)
	if(!is_numeric($words[$i])){$found=true;break;}
    if(!$found)return reject("NUMERIC",$words);

    return true;    
}

