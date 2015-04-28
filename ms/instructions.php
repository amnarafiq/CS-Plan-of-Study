<?php
require_once("../vendor/autoload.php");
require_once("../../dept_data.php");
date_default_timezone_set('America/New_York');

$val['director'] = $director;
$val['depthead'] = $depthead;
$val['coordinator'] = $coordinator;
$val['today'] = date("m/d/Y");

echo gen_template("instructions.tmpl", $val);
?>