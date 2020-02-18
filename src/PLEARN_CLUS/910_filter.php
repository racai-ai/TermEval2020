<?php

require "common.php";

//$terms1=loadList("my/en/dt1/equi/annotations_terms.ann");
//$terms2=loadList("my/en/dt2/equi/annotations_terms.ann");
//$terms3=loadList("my/en/dt3/equi/annotations_terms.ann");
//$terms3=[];

//$terms=array_merge($terms1,$terms2,$terms3);

foreach(["c_my","c_crawl","c_lemma"] as $method){
    foreach(["equi","wind","corp","hf"] as $corpus){

    $terms=loadList("my/en/$method/$corpus/annotations_terms.ann");
    
    echo "Filtering $method => $corpus\n";
    echo "    Initial terms=".count($terms)."\n";



/*foreach($terms as $k=>$t){
    $w=explode(" ",$t);
    if(count($w)>1)continue;
    
    foreach($terms as $k1=>$t1){
	$w1=explode(" ",$t1);
	if(count($w1)==1)continue;
	
	$found=false;
	foreach($w1 as $w1w)if($w1w==$w[0])$found=true;
	
	if($found){unset($terms[$k]); break;}
    }
}*/

$chars=[",",".","-",":","'",";","(",")"];

foreach($terms as $k=>$t){
    foreach($chars as $c)
	if(startsWith($t,$c) || endsWith($t,$c)){
	    unset($terms[$k]);break;
	}
}

echo "    Final terms=".count($terms)."\n";

$terms=array_flip($terms);
saveList($terms,"my/en/$method/$corpus/annotations_terms.ann");

}
}
