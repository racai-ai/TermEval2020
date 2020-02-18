<?php

die ("NOT USED");

require "common.php";

$stat=loadStat("my/${CORPUSLANG}/kl_equi_stat.txt");

$n=3*count($stat)/100;

$stoplist=[];
for($i=0;$i<$n;$i++){
    $t=array_keys($stat)[count($stat)-1-$i];
    $stoplist[$t]=$stat[$t];
}

saveStat($stoplist,"my/${CORPUSLANG}/stoplist.txt");
saveStat($stoplist,"my/${CORPUSLANG}/stoplist2.txt");
saveList($stoplist,"my/${CORPUSLANG}/stop_words.txt");