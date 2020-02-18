<?php

die("NOT USED");

require_once "common.php";

$start=loadStat("ann_start.txt");
$end=loadStat("ann_end.txt");
$contains=loadStat("ann_contains.txt");
$stoplist=loadStat("stoplist.txt");

$grams4=loadStat("stats_4_file_lower.txt");

$candidates=[];

foreach($grams4 as $g=>$v){
    $words=explode(" ",$g);
    
    if(isset($stoplist[$words[0]]) && !isset($start[$words[0]]))continue;
    if(isset($stoplist[$words[count($words)-1]]) && !isset($end[$words[count($words)-1]]))continue;
    
    $found=false;
    for($i=1;$i<count($words)-1;$i++)
	if(isset($stoplist[$words[$i]]) && !isset($contains[$words[$i]])){$found=true;break;}
    if($found)continue;
    
    for($i=0;$i<count($words);$i++)
	if(strlen($words[$i])>1){$found=true;break;}
    if(!$found)continue;

    $found=false;
    for($i=0;$i<count($words);$i++)
	if(!is_numeric($words[$i])){$found=true;break;}
    if(!$found)continue;

    
    $candidates[$g]=$v;
}

saveStat($candidates,"candidates_4.txt");
