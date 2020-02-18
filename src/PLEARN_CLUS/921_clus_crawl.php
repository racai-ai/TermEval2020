<?php

require "common.php";

//$terms1=loadList("my/en/dt1/equi/annotations_terms.ann");
//$terms2=loadList("my/en/dt2/equi/annotations_terms.ann");
//$terms3=loadList("my/en/dt3/equi/annotations_terms.ann");
//$terms3=[];

//$terms=array_merge($terms1,$terms2,$terms3);


$embeddings=[];
$embeddingsNF=[]; // emb not found
$dist=[];

echo "Loading embeddings index\n";
/*if(is_file("embeddings.txt")){
    eval("\$embeddings=".file_get_contents("embeddings.txt").";");
}*/
$fembeddings=fopen("/data/wordembeddings/crawl-300d-2M.vec","rb");
$findex=fopen("/data/wordembeddings/crawl-300d-2M.vec.index","r");
$embeddingsIndex=[];
$n=0;
while(!feof($findex)){
    $line=fgets($findex);
    if($line===false)break;
    $line=explode(" ",trim($line));
    if(count($line)!=2)continue;
    $n++;
    if($n%10000 == 0)echo "Line $n\n";
    $embeddingsIndex[$line[0]]=intval($line[1]);
}
fclose($findex);

/*echo "Loading dist\n";
if(is_file("dist.txt")){
    eval("\$dist=".file_get_contents("dist.txt").";");
}*/

function getEmbeddings($word){
    global $embeddings,$embeddingsNF,$embeddingsIndex,$fembeddings;
    
    if(isset($embeddings[$word]))return $embeddings[$word];
    
    $data=[];
    if(!isset($embeddingsIndex[$word])){
	$embeddingsNF[$word]=true;
	echo "Embeddings not found [$word]\n";
	for($i=0;$i<300;$i++)$data[$i]=floatval(0.0);
    }else{
	fseek($fembeddings,$embeddingsIndex[$word],SEEK_SET);
	$data=fgetcsv($fembeddings,0," ");
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

foreach(["equi","corp","wind","hf"] as $corpus){

echo "Processing corpus $corpus\n";
$terms=loadList("my/en/my/$corpus/annotations_terms.ann");

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


/* BEST FOR DT + oanc emb:
$EPS=0.6;
$MINPTS=10;
*/

// 0.7 , 10 => 36.44
$EPS=0.5;
$MINPTS=5;


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

@mkdir("my/en/c_crawl/");
@mkdir("my/en/c_crawl/$corpus");
saveList($terms,"my/en/c_crawl/$corpus/annotations_terms.ann");

}

fclose($fembeddings);
