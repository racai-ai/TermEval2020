<?php

require "common.php";

$fout=fopen("my/$CORPUSLANG/wordembeddings.train","w");
$firstword=true;

function processFile($fname){
    global $fout,$firstword;
    
    echo "$fname\n";
    
    foreach(explode("\n",file_get_contents($fname)) as $fline){
	foreach(getSentences(trim(mb_strtolower($fline))) as $line){
	    foreach(getWords($line) as $word){
		if(strlen($word)==0)continue;
		
		if($firstword)$firstword=false;
		else fwrite($fout," ");
		fwrite($fout,$word);
	    }
	}
    }
}

$num=0;
$num+=processFolder("$CORPUSBASE/$CORPUSLANG/equi/texts/annotated/");
$num+=processFolder("$CORPUSBASE/$CORPUSLANG/equi/texts/unannotated/");
$num+=processFolder("$CORPUSBASE/$CORPUSLANG/corp/texts/annotated/");
$num+=processFolder("$CORPUSBASE/$CORPUSLANG/corp/texts/unannotated/");
$num+=processFolder("$CORPUSBASE/$CORPUSLANG/wind/texts/annotated/");
$num+=processFolder("$CORPUSBASE/$CORPUSLANG/wind/texts/unannotated/");
$num+=processFolder("$CORPUSBASE/$CORPUSLANG/hf/texts/annotated/");

fclose($fout);
