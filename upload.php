<?php
require_once("../vendor/autoload.php");
require_once("../dept_data.php");
date_default_timezone_set('America/New_York');


$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);

if ($extension == "pos") {
  if ($_FILES["file"]["error"] > 0) {
    echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
  }
  else {
    echo "Upload: " . $_FILES["file"]["name"] . "<br>";
    echo "Type: " . $_FILES["file"]["type"] . "<br>";
    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
    echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";

    $data = file_get_contents($_FILES["file"]["tmp_name"]);
    $val = json_decode($data, true);


	$val['director'] = $director;
	$val['depthead'] = $depthead;
	$val['coordinator'] = $coordinator;
	$val['today'] = date("m/d/Y");

	echo gen_template("phd/index.tmpl", $val);


    // if (file_exists("upload/" . $_FILES["file"]["name"])) {
    //   echo $_FILES["file"]["name"] . " already exists. ";
    // }
    // else {
    //   move_uploaded_file($_FILES["file"]["tmp_name"],
    //   	"upload/" . $_FILES["file"]["name"]);
    //   echo "Stored in: " . "upload/" . $_FILES["file"]["name"];
    // }
 }
  }
else
  {
  echo "Invalid file";
  }

?>