<?php
// File: index.php
// Description: Basic index file, just displays the template.
// Author: mapq
//
require_once("../vendor/autoload.php");
date_default_timezone_set('America/New_York');

$val['today'] = date("m/d/Y");

echo gen_template("index.tmpl", $val);
?>