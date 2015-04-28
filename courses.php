<?php
require_once("../vendor/autoload.php");
require_once("../dept_data.php");
// $vars = get_template_vars("index.tmpl");

$data = array();
foreach($areas as $k => $d) {
	$data['courses'][] = array('course'=>$k, 'area'=>$areatitles[$d['area']], 'title'=>$d['title']);
}
echo gen_template("courses.tmpl", $data);

?>