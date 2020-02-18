<?php

die("NOT USED");

require_once "common.php";

$stoplist=[];

$max=intval(file_get_contents("my/$CORPUSLANG/num_max_files.txt"));

$s1=loadStat("my/$CORPUSLANG/stats_1_file_lower.txt");

foreach(explode("\n",file_get_contents("my/$CORPUSLANG/stats_1_file_lower.txt")) as $line){

    $line=trim($line);
    if(strlen($line)==0)continue;
    
    $data=explode(";",$line);
    if(count($data)!=2)continue;
    
    $w=$data[0];
    if(intval($data[1])>$max)$stoplist[$w]=$data[1];

}

$c1=loadStat("my/$CORPUSLANG/equi_lower.txt");
$c2=loadStat("my/$CORPUSLANG/wind_lower.txt");
$c3=loadStat("my/$CORPUSLANG/corp_lower.txt");
$c4=loadStat("my/$CORPUSLANG/hf_lower.txt");

$stoplist2=[];
foreach($c1 as $w=>$v){
    if(isset($c2[$w]) && isset($c3[$w]) && isset($s1[$w]) && $s1[$w]>($max/2)){
	$stoplist2[$w]=$v;
	echo "$w ".$c1[$w]." ".$c2[$w]." ".$c3[$w]." ".$s1[$w]."\n";
    }
}

saveStat($stoplist,"my/$CORPUSLANG/stoplist.txt");
saveStat($stoplist2,"my/$CORPUSLANG/stoplist2.txt");

//$swords=array_merge($stoplist,$stoplist2);
$swords=$stoplist;
foreach($swords as $k=>$v){
    if(strlen($k)==1)unset($swords[$k]);
}
saveList($swords,"my/$CORPUSLANG/stop_words.txt");
