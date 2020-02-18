<?php

require "common.php";

//$terms1=loadList("my/en/dt1/equi/annotations_terms.ann");
//$terms2=loadList("my/en/dt2/equi/annotations_terms.ann");
//$terms3=loadList("my/en/dt3/equi/annotations_terms.ann");
//$terms3=[];

//$terms=array_merge($terms1,$terms2,$terms3);

$wlemma=loadStat("my/en/words_lemma.txt");


$embeddings=[];
$embeddingsNF=[]; // emb not found
$dist=[];

echo "Loading embeddings\n";
/*if(is_file("embeddings.txt")){
    eval("\$embeddings=".file_get_contents("embeddings.txt").";");
}*/
$fp=fopen("my/en/wordembeddings_conll_lemma.vec","r");
while(!feof($fp)){
    $line=fgets($fp);
    if($line===false)break;
    $line=explode(" ",trim($line));
    if(count($line)!=101)continue;
    $data=array_slice($line,1);
    foreach($data as $k=>$v)$data[$k]=floatval($v);
    $embeddings[$line[0]]=$data;
}
fclose($fp);

/*echo "Loading dist\n";
if(is_file("dist.txt")){
    eval("\$dist=".file_get_contents("dist.txt").";");
}*/

function getEmbeddings($word){
    global $embeddings,$embeddingsNF;
    
    if(isset($embeddings[$word]))return $embeddings[$word];
    
    $embeddingsNF[$word]=true;

    echo "Retrieve emb for [$word]\n";
    
//    $data=file_get_contents("http://89.38.230.23/word_embeddings/ws/wordvectors_get.php?w1=${word}");
//    $data=explode(" ",trim($data));
    $data=[];
    if(count($data)<100){
	$data=[];
	for($i=0;$i<100;$i++)$data[$i]=floatval(0.0);
    }else{
	$data=array_slice($data,1);
	if(count($data)!=300){var_dump($data);die();}
	foreach($data as $k=>$v)$data[$k]=floatval($v);
    }
    
    $embeddings[$word]=$data;
    return $data;
}

function getDistance($w1,$w2){
    global $dist;
    
    if(isset($dist["$w1|$w2"]))return $dist["$w1|$w2"];
    
    if(isset($dist["$w2|$w1"])){
	$dist["$w1|$w2"]=$dist["$w2|$w1"];
	return $dist["$w1|$w2"];
    }
    
    $e1=getEmbeddings($w1);
    $e2=getEmbeddings($w2);
    
    $s1=0.0;
    $s2=0.0;
    $s3=0.0;
    for($i=0;$i<count($e1);$i++){
	$s1+=$e1[$i]*$e2[$i];
	$s2+=$e1[$i]*$e1[$i];
	$s3+=$e2[$i]*$e2[$i];
    }
    if($s2==0 || $s3==0)$d=0;
    else $d=$s1/(sqrt($s2)*sqrt($s3));
    $dist["$w1|$w2"]=$d;
    return $d;
}

foreach(["equi","wind","corp","hf"] as $corpus){

echo "Processing $corpus\n";
$terms=loadList("my/en/my/equi/annotations_terms.ann");


$tlemma=[];
$initialTerms=$terms;

foreach($terms as $t){
    if(isset($wlemma[$t]))
	$tlemma[]=$wlemma[$t];
}
$terms=$tlemma;

$terms=array_flip($terms);
//$terms=array_flip(["horse","rider","crop","saddle"]);
foreach($terms as $t=>$l){
    if(strpos($t," ")!==false)unset($terms[$t]);
    else $terms[$t]=-1;
}

/*$d1=getDistance("horse","rider");
echo "d1=$d1\n";
$d1=getDistance("horse","trompet");
echo "d1=$d1\n";
die();
*/


/* BEST FOR DT:
$EPS=0.6;
$MINPTS=10;
*/

// 0.7 , 10 => 36.44
// 0.6 , 60 => 36,63
// 0.5 , 500 => 36,69
// dt+myembdd 0.7,600 => 35
// my+myembconll 0.85,150 => 28,94 R=74
// my+myembconll 0.88,100 => 30,57 R=44,27
// dt+myembconll 0.8,10 => 34,9
// dt+myembconll 0.85,50 => 35,24
// dt+myembconll 0.85,100 => 36,19
// my+emblemma 0.85,150 => 36,05
$EPS=0.87;
$MINPTS=100;


echo "Clustering EPS=$EPS MINPTS=$MINPTS\n";


$C=0;
for($tindex=0;$tindex<count($terms);$tindex++){
    $t=array_keys($terms)[$tindex];
    $l=$terms[$t];

    if($l!=-1)continue;
    
    $N=[];
    foreach($terms as $t1=>$l1){
	if($t!=$t1 && getDistance($t,$t1)>$EPS)$N[]=$t1;
    }
    
    if(count($N)<$MINPTS){
	$terms[$t]=-2; // noise
	continue;
    }
    
    $C++;
    echo "C=$C\n";
    $terms[$t]=$C;
    for($i=0;$i<count($N);$i++){
	$tn=$N[$i];
	if($terms[$tn]==-2)$terms[$tn]=$C;
	if($terms[$tn]!=-1)continue;
	
	$terms[$tn]=$C;
	$N1=[];
	foreach($terms as $t2=>$l2){
	    if($t2!=$tn && getDistance($tn,$t2)>$EPS)$N1[]=$t2;
	}
	if(count($N1)>=$MINPTS){
	    foreach($N1 as $tn1){
		$found=false;
		foreach($N as $tn2)if($tn1==$tn2){$found=true;break;}
		if(!$found)$N[]=$tn1;
	    }
	}
    }
}

saveStat($terms,"clus.ann");

$clusterSizes=[];
foreach($terms as $t=>$c){
    if(!isset($clusterSizes[$c]))$clusterSizes[$c]=0;
    $clusterSizes[$c]++;
}
var_dump($clusterSizes);

$largestCluster=-1;
foreach($clusterSizes as $c=>$s){
    if($c>0 && ($largestCluster<0 || $s>$clusterSizes[$largestCluster]))$largestCluster=$c;
}

echo "Largest cluster = ${largestCluster} [".$clusterSizes[$largestCluster]."]\n";

echo "Initial count=".count($terms)."\n";
foreach($terms as $t=>$c){
    if(($c==-2 || $c!=$largestCluster) && !isset($embeddingsNF[$t]))unset($terms[$t]);
}
echo "Final count=".count($terms)."\n";

//file_put_contents("embeddings.txt",var_export($embeddings,true));
//file_put_contents("dist.txt",var_export($dist,true));

$finalTerms=[];
foreach($initialTerms as $t){
    if(isset($wlemma[$t]) && isset($terms[$wlemma[$t]]))
	$finalTerms[$t]=true;
}

@mkdir("my/en/c_lemma");
@mkdir("my/en/c_lemma/$corpus");
saveList($finalTerms,"my/en/c_lemma/$corpus/annotations_terms.ann");

}
