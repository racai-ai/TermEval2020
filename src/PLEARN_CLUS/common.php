<?php

$CORPUSBASE="ACTER_version1_1_incl_test";
$CORPUSLANG="en";

$combinations=[
//"my_tfidff_rake_yake_textrank" => ["tfidff","rake","yake","trank","my"],
//"tfidff_rake_yake_textrank" => ["tfidff","rake","yake","trank"],
//"my_rake_yake" => ["my","rake","yake"],
//"rake_yake" => ["rake","yake"],
//"my_rakeo_yakeo" => ["my","rakeo","yakeo"],
//"rakeo_yakeo" => ["rakeo","yakeo"],
//"my_tfidff_rake_yake" => ["tfidff","rake","yake","my"],
"mytr_rakeo_yakeo_tfidf" => ["mytr","rakeo","yakeo","tfidf"],
"mytr_rakeo_yakeo_tfidf_kl" => ["mytr","rakeo","yakeo","tfidf","kl"],
"mytr_rakeo_yakeo_tfidf_c_my" => ["mytr","rakeo","yakeo","tfidf","c_my"],

"radu_my_tfidf_mytr" => ["radu","c_my","mytr","tfidff","rakeo","yakeo","kl"],


"radu_best" => ["radu","c_oanc","tfidff","+2:radu","+3:radu"],
"radu_pp_oanc" => ["radu","pp_c_oanc","tfidff","+1:pp_c_oanc","+2:radu","+3:radu"],
"radu_pp_oanc2" => [
    "radu","pp_c_oanc","tfidff",
    "+1:pp_c_oanc","+1:radu_best","2:radu_my_tfidf_mytr","+3:radu"],

//"radu_pp_oanc3" => ["+1:radu_best","+2:radu_pp_oanc2","+3:radu_best"],


"radu_extra" => ["+radu_best","+4:radu_my_tfidf_mytr"],

];


@mkdir("my");
@mkdir("my/$CORPUSLANG");

//$wordBoundaryS=" ,!?();:/\\-[]—";
$wordBoundaryS=" ,;:/\\()[]—";
$wordBoundary=[];
for($i=0;$i<mb_strlen($wordBoundaryS);$i++)$wordBoundary[mb_substr($wordBoundaryS,$i,1)]=true;


$endings=[];
$roots=[];
$roots_r=[];

function loadRootsEndings(){
global $endings,$roots,$roots_r,$CORPUSLANG;
$endings=loadStat("my/$CORPUSLANG/endings.txt");
$endings=array_slice($endings,0,25);

$roots=loadStat("my/$CORPUSLANG/roots.txt");
$roots_r=[];
foreach($roots as $w=>$r){
    $found=false;
    foreach($endings as $e=>$n)
        if(endsWith($w,$e) && strlen($w)-strlen($r)==strlen($e)){$found=true;break;}

    if(!$found){
        unset($roots[$w]);
        continue;
    }

    if(!isset($roots_r[$r]))$roots_r[$r]=0;
    $roots_r[$r]++;
}
}

function getSentences($fline){
    $sent=[];
    $prev=0;
    for($i=0;$i<mb_strlen($fline);$i++){
	if(preg_match("/^[.][ ]+[A-Z]/",mb_substr($fline,$i,100))===1){
    //foreach(preg_split("/[.][ ]+[A-Z]/",$fline) as $line){
        //$line=preg_replace("/[^-'a-zA-Z0-9]/"," ",$line);
/*      $l="";
        for($i=0;$i<mb_strlen($line);$i++){
            $c=mb_substr($line,$i,1);
            if(isset($letters[$c]))$l.=$c;
            else $l.=" ";
        }
        $line=$l;
*/      
	    $line=mb_substr($fline,$prev,$i-$prev);
	    $prev=$i+1;
	    $line=preg_replace("/[ ]+/"," ",$line);
    	    $line=trim($line,".");
    	    $line=trim($line);
    	    $line=trim($line,".");
    	    if(strlen($line)>0)$sent[]=$line;
	}
    }

	    $line=mb_substr($fline,$prev);
	    $line=preg_replace("/[ ]+/"," ",$line);
    	    $line=trim($line,".");
    	    $line=trim($line);
    	    $line=trim($line,".");
    	    if(strlen($line)>0)$sent[]=$line;

    
    return $sent;
}

function getWords($line){
    global $wordBoundary;
        $words=[];
        $w="";
        for($i=0;$i<mb_strlen($line);$i++){
            $c=mb_substr($line,$i,1);
            if(isset($wordBoundary[$c])){
                if(strlen($w)>0){
            	    $words[]=$w;
            	}
                $w="";
                if($c!=" ")$words[]=$c;
            }else $w.=$c;
        }
        if(strlen($w)>0)$words[]=$w;
        return $words;
}

function loadStat($fname,$separator=";"){
    $ret=[];
    foreach(explode("\n",file_get_contents($fname)) as $line){
	$line=trim($line);
	if(strlen($line)==0)continue;
	$data=explode($separator,$line);
	if(count($data)!=2)continue;
	$ret[$data[0]]=$data[1];
    }
    return $ret;
}


function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function processFolder($folder,$ext=".txt",$callback="processFile"){
    $num=0;
    $dh = opendir($folder);
    while (($file = readdir($dh)) !== false) {
	$fpath="$folder/$file";
	if(is_file($fpath) && endsWith($fpath,$ext)){
	    $callback($fpath);
	    $num++;
	}
    }
    closedir($dh);
    return $num;
}

function saveStat(&$vec, $fname){
    arsort($vec);

    file_put_contents($fname,"");
    foreach($vec as $w=>$n){
	file_put_contents($fname,"$w;$n\n",FILE_APPEND);
    }

}

function saveList(&$vec, $fname){
    arsort($vec);

    file_put_contents($fname,"");
    foreach($vec as $w=>$n){
	file_put_contents($fname,"$w\n",FILE_APPEND);
    }

}

function loadList($fname){
    $ret=[];
    foreach(explode("\n",file_get_contents($fname)) as $line){
	$line=trim($line);
	if(strlen($line)>0)$ret[]=$line;
    }
    return $ret;
}
