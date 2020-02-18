<?php

require_once "common.php";

$start=loadStat("my/$CORPUSLANG/ann_start.txt");
$end=loadStat("my/$CORPUSLANG/ann_end.txt");
$contains=loadStat("my/$CORPUSLANG/ann_contains.txt");
$contains_2g=loadStat("my/$CORPUSLANG/ann_contains_2g.txt");
$stoplist1=loadStat("my/$CORPUSLANG/stoplist.txt");
$stoplist2=loadStat("my/$CORPUSLANG/stoplist2.txt");
$stoplist=array_merge($stoplist1,$stoplist2);
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

loadRootsEndings();

$termEndings_start=loadStat("my/$CORPUSLANG/ann_endings_start.txt");
$termEndings_end=loadStat("my/$CORPUSLANG/ann_endings_end.txt");
$termEndings_contains=loadStat("my/$CORPUSLANG/ann_endings_contains.txt");


$ann=[];

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

function processCandidate($words,$next,$before){
    global $ann,$stoplist,$start,$end,$contains,$letters,$stoplist1,$stoplist2;
    global $start1,$start2,$start3p,$end2,$end3p;
    global $afterTerm,$beforeTerm;
    global $stat2f,$num_max_files,$contains_2g;
    global $endings,$roots,$roots_r;
    global $termEndings_start,$termEndings_end,$termEndings_contains;
    
    if(count($words)<1)return false; // currently only 2,3,4 grams
    
    foreach($words as $w){
	for($i=0;$i<mb_strlen($w);$i++){
	    $l=mb_substr($w,$i,1);
	    if(!isset($letters[$l]))return reject("letters",$words);
	}
    }
    
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
    
    // Check : WORD PREP WORD WORD => nu prea produce rezultate bune
    // se pare ca sunt multe situatii gen "work on the ground" vs "work on the refinement"...
/*    for($i=1;$i<count($words)-1;$i++){
	if(isset($stoplist1[$words[$i]])){
	    $sf=getStatsf($words);
	    $s1f=getStatsf(array_slice($words,0,$i));
	    $s2f=getStatsf(array_slice($words,$i+1));

	    $s=getStats($words);
	    $s1=getStats(array_slice($words,0,$i));
	    $s2=getStats(array_slice($words,$i+1));
	    
	    if($s1f>60 || $s2f>60){
//		echo "$i => ".implode(" ",$words)."  [$i F:$sf $s1f $s2f W:$s $s1 $s2]\n";
		
		return reject("STOP6",$words,"[$i F:$sf $s1f $s2f W:$s $s1 $s2]");
	    }
	}
    }
*/
    
    // check for TERM PREP WORD
    if(count($next)>0){
	$nw=$next[0];
	if(isset($stoplist[$nw]) && !isset($afterTerm[$nw]))
	    return reject("NOTAFTER",$words,$nw);
    }

    // check for PREP TERM
    if(count($before)>0){
	$bw=$before[count($before)-1];
	if(isset($stoplist[$bw]) && !isset($beforeTerm[$bw]))
	    return reject("NOTBEFORE",$words,$bw);
    }
    
    // check for internal 2-grams
    if(count($words)>=3){
	for($i=1;$i<=count($words)-3;$i++){
	    $g=implode(" ",array_slice($words,$i,2));
	    if(isset($stat2f[$g]) && $stat2f[$g]>$num_max_files && !isset($contains_2g[$g]))
		return reject("INSIDE2GRAM",$words,$g);
	}
    }

    // check for first word accepting multiple endings
    $w=$words[0];
    if(isset($roots[$w])){
	$r=$roots[$w];
	if($roots_r[$r]>4)
	    return reject("MULTIROOTS",$words,implode(" ",[$w,$r,$roots_r[$r]]));
    }
    
    // check the allowed term endings
    for($i=0;$i<count($words);$i++){
	if(isset($roots[$words[$i]])){
	    $elen=strlen($words[$i])-strlen($roots[$words[$i]]);
	    $ending=substr($words[$i],strlen($words[$i])-$elen);
	    if($i==0){
		if(!isset($termEndings_start[$ending]))
		    return reject("ENDINGSTART",$words,$ending);
	    }else if($i==count($words)-1){
		if(!isset($termEndings_end[$ending]))
		    return reject("ENDINGEND",$words,$ending);
	    }else{
		if(!isset($termEndings_contains[$ending]))
		    return reject("ENDINGCONTAINS",$words,$ending);
	    }
	}
    }
    
    $ann[implode(" ",$words)]=true;
    return true;
}



//$wordBoundaryS=" ,!?();:/\\-[]—";
/*$wordBoundaryS=" ,;:/\\()[]—";
$wordBoundary=[];
for($i=0;$i<mb_strlen($wordBoundaryS);$i++)$wordBoundary[mb_substr($wordBoundaryS,$i,1)]=true;
*/

function processFile($fname){
    echo "$fname\n";
    global $ann,$wordBoundary;

    foreach(explode("\n",file_get_contents($fname)) as $fline){
	foreach(getSentences($fline) as $line){
	$grams2=[];
	$grams3=[];
	$grams4=[];
	$grams5=[];
	
	$words=getWords($line);
	for($wordIndex=0;$wordIndex<count($words);$wordIndex++){
	    $word=$words[$wordIndex];
	    if(strlen($word)==0)continue;
	    
	    $nextWords=array_slice($words,$wordIndex+1,5);
	    $beforeWords=[];
	    
	    $wordLower=mb_strtolower($word);

	    $num=min(5,$wordIndex);
	    if($num>0)$beforeWords=array_slice($words,$wordIndex-$num,$num);
	    else $beforeWords=[];
	    processCandidate([$wordLower],$nextWords,$beforeWords);
	    
	    $grams2[]=$wordLower;
	    if(count($grams2)==2){
		$num=min(5,$wordIndex-1);
		if($num>0)$beforeWords=array_slice($words,$wordIndex-1-$num,$num);
		else $beforeWords=[];
		processCandidate($grams2,$nextWords,$beforeWords);
		array_shift($grams2);
	    }


	    $grams3[]=$wordLower;
	    if(count($grams3)==3){
		$num=min(5,$wordIndex-2);
		if($num>0)$beforeWords=array_slice($words,$wordIndex-2-$num,$num);
		else $beforeWords=[];
		processCandidate($grams3,$nextWords,$beforeWords);
		array_shift($grams3);
	    }

	    $grams4[]=$wordLower;
	    if(count($grams4)==4){
		$num=min(5,$wordIndex-3);
		if($num>0)$beforeWords=array_slice($words,$wordIndex-3-$num,$num);
		else $beforeWords=[];
		processCandidate($grams4,$nextWords,$beforeWords);
		array_shift($grams4);
	    }

	    $grams5[]=$wordLower;
	    if(count($grams5)==5){
		$num=min(5,$wordIndex-4);
		if($num>0)$beforeWords=array_slice($words,$wordIndex-4-$num,$num);
		else $beforeWords=[];
		processCandidate($grams5,$nextWords,$beforeWords);
		array_shift($grams5);
	    }

	}
    }
    }

}


function processFile2($fname){
    echo "2\t$fname\n";
    global $ann,$wordBoundary,$annStat;

    $data=mb_strtolower(file_get_contents($fname));
    foreach($ann as $term=>$v){
	if(!isset($annStat[$term]))$annStat[$term]=0;
	$annStat[$term]+=substr_count($data,$term);
    }
}

@mkdir("my");
@mkdir("my/$CORPUSLANG/my");

foreach(["equi","corp","wind","hf"] as $corpus){
@mkdir("my/$CORPUSLANG/my/$corpus");

$ann=[];
$num=processFolder("$CORPUSBASE/$CORPUSLANG/$corpus/texts/annotated/");
$annStat=[];
$num=processFolder("$CORPUSBASE/$CORPUSLANG/$corpus/texts/annotated/",".txt","processFile2");

$annStat2=$annStat;

$annStatK=array_keys($annStat);
for($i=0;$i<count($annStat);$i++){
    $term=$annStatK[$i];
    $candidates=[];
    for($j=0;$j<count($annStat);$j++){
	if($i==$j)continue;
	$t1=$annStatK[$j];
	if(substr_count($t1,$term)>0)$candidates[]=$t1;
    }
    
    $s=0;
    foreach($candidates as $c)$s+=$annStat[$c];
    
    if(count($candidates)>0){
	$annStat2[$term]-= floatval($s) / floatval(count($candidates));
    }
}

$annStat=$annStat2;

foreach($annStat as $term=>$v){
    $annStat[$term]=log(count(getWords($term)),2)*$v;
}
saveStat($annStat,"my/$CORPUSLANG/my/$corpus/annotations_terms.annstat");
foreach($annStat as $term=>$v){
    if($v<0)unset($ann[$term]); // so far good for 3
}
saveList($ann,"my/$CORPUSLANG/my/$corpus/annotations_terms.ann");

}
