<?php

require_once "common.php";

echo "Used only to convert stop_words.txt into stoplist.txt\n";

$sl=loadList("my/$CORPUSLANG/stop_words.txt");
$sl=array_flip($sl);
saveStat($sl,"my/$CORPUSLANG/stoplist.txt");
saveStat($sl,"my/$CORPUSLANG/stoplist2.txt");
