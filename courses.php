<?php
// File: courses.php
// Description: Generates a courses page with all the courses from
// the global constants file (dept_data.php)
// Author: mapq
//

require_once("../vendor/autoload.php");
require_once("../dept_data.php");
// $vars = get_template_vars("index.tmpl");

$data = array();
foreach($areas as $k => $d) {
	$data['courses'][] = array('course'=>$k, 'area'=>$areatitles[$d['area']], 'title'=>$d['title']);
}
echo gen_template("courses.tmpl", $data);
?>