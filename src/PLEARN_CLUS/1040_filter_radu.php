<?php

require "common.php";

$t1=loadStat("my/en/radu/equi-terms-0.txt","\t");
@mkdir("my/en/radu/equi");
saveList($t1,"my/en/radu/equi/annotations_terms.ann");

$t1=loadStat("my/en/radu/corp-terms-0.txt","\t");
@mkdir("my/en/radu/corp");
saveList($t1,"my/en/radu/corp/annotations_terms.ann");

$t1=loadStat("my/en/radu/wind-terms-0.txt","\t");
@mkdir("my/en/radu/wind");
saveList($t1,"my/en/radu/wind/annotations_terms.ann");

//$t1=loadStat("my/en/radu/hf-terms2-sorted.txt","\t");
$t1=loadStat("my/en/radu/hf-terms-0.txt","\t");
@mkdir("my/en/radu/hf");
saveList($t1,"my/en/radu/hf/annotations_terms.ann");
