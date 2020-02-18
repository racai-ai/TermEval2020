<?php

require "common.php";

$ann=[];

function processFile($fname){
    global $ann,$CORPUSLANG,$destDir;

    echo "$fname\n";
    shell_exec("PATH=\$PATH:/data/programs/jdk1.8.0_231/bin /data/programs/stanford-corenlp-full-2018-10-05/corenlp.sh  -annotators tokenize,ssplit,pos,lemma -outputFormat conll -file $fname -outputDirectory $destDir");
}

@mkdir("my");
@mkdir("my/$CORPUSLANG/corenlp");

foreach(["equi","corp","wind","hf"] as $corpus){
    @mkdir("my/$CORPUSLANG/corenlp/$corpus");
    $destDir="my/$CORPUSLANG/corenlp/$corpus/";
    $num=processFolder("$CORPUSBASE/$CORPUSLANG/$corpus/texts/annotated/");
}
