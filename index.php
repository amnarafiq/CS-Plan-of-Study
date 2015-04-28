<?php
require_once("../vendor/autoload.php");
date_default_timezone_set('America/New_York');

$val['today'] = date("m/d/Y");

echo gen_template("index.tmpl", $val);
?>