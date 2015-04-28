<?php
// File: index.php
// Project: CS@VT Plan of Study
// Author: manuel a perez-quinones
// Description: MS Plan of study home page
//

require_once("../../vendor/autoload.php");
require_once("../../dept_data.php");
require_once("../library.php");
date_default_timezone_set('America/New_York');

// @@ Total of 5000 credits (javascript) on page is not updating when updated on screen

// Differences between MS Thesis and Coursework
// @@ 5994 vs 5974, course numbers
// @@ 5994 vs 5974, course titles
// @@ 5994 vs 5974, number of credits
// @@ Courses not allowed in either degree
// @@ Max number of credits (30 or 33)
// @@ add a message on change of program to reload page (maybe force a post of the page?)
// @@ Update for MS - not allowed to have any 5974

define(DEGREE_NAME, "MS");		// was PhD
define(DEGREE_MIN_CR, 30);		// was 90 for phd
define(BREADTH_COURSES, 4);		// was 5 for phd
define(TRANSFER_LIMIT_CR, 9);	// 15 FOR PHD
define(SIXTHOUSAND_MIN_CR, 3);	// min 1
define(SIXTHOUSAND_MAX_CR, -1);	// no max
define(COGNATE_MIN_CR, 0);		// NOT USED IN MS THESIS
define(COGNATE_MAX_CR, 0);
define(SENIOR_MIN_CR, 0);
define(SENIOR_MAX_CR, 3);



// BEGINNING of the main program
// If we have a post request ...
if (($_SERVER["REQUEST_METHOD"] == "POST") && ($_POST['type'] == "upload")) {
	$temp = explode(".", $_FILES["file"]["name"]);
	$extension = end($temp);
	if ($extension == "pos") {
		if ($_FILES["file"]["error"] > 0) {
			$val = array();
			$val['top_messages'][] = array('message'=>"Error uploading file. Return Code: " . $_FILES["file"]["error"]);
		}
		else {
			// echo "Upload: " . $_FILES["file"]["name"] . "<br>";
			// echo "Type: " . $_FILES["file"]["type"] . "<br>";
			// echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
			// echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";

			$data = file_get_contents($_FILES["file"]["tmp_name"]);
			$val = json_decode($data, true);
		}

		$val['director'] = $director;
		$val['depthead'] = $depthead;
		$val['coordinator'] = $coordinator;
		$val['printed'] = date("m/d/Y");
		echo gen_template("ms.tmpl", $val);
	}
	else {
		$val = array();
		$val['bad_messages'][] = array('message'=>"Error uploading file. The file extension must be .pos");
		$val['printed'] = date("m/d/Y");
		echo gen_template("ms.tmpl", $val);
	}
}

// Validation... 
else if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// print_r($_POST);

	// normalize choice for the degree type
	if ($_POST['msthesis'] == 1) {
		$_POST['mscoursework'] = 0;
		// for THESIS
		define(RESEARCH_MIN_CR, 6);	// 30 FOR PHD
		define(RESEARCH_MAX_CR, 9);	// 1000 FOR PHD (NO LIMIT)
		define(RESEARCH_COURSE, "CS5994");
		define(RESEARCH_COURSE_TITLE, "CS5994 Research and Thesis");
		define(IND_STUDY_MIN_CR, 0);	// INDEPENDENT STUDY
		define(IND_STUDY_MAX_CR, 0);
	}
	else {
		// Coursework
		$_POST['msthesis'] = 0;		// redundant
		$_POST['mscoursework'] = 1;
		// for coursework
		define(RESEARCH_MIN_CR, 0);
		define(RESEARCH_MAX_CR, 0);
		define(IND_STUDY_MIN_CR, 3);	// INDEPENDENT STUDY
		define(IND_STUDY_MAX_CR, 3);
		define(RESEARCH_COURSE, "CS5974");
		define(RESEARCH_COURSE_TITLE, "CS5974 Independent Study");
	}

	// Before echoing it back up, do some clean up and normalization
	// of course numbers, all upper case and remove the spaces
	$courseGroups = array('fivethousand', 'sixthousand', 'cognate', 'senior');
	foreach ($courseGroups as $idx) {
		if (isset($_POST[$idx])) {
			foreach($_POST[$idx] as $k=>$v) {
				$_POST[$idx][$k]['term'] = normalize_term($_POST[$idx][$k]['term']);
				$_POST[$idx][$k]['course'] = normalize_course($_POST[$idx][$k]['course']);
				$_POST[$idx][$k]['title'] = get_course_title($idx, $_POST[$idx][$k]['course']);
				$_POST[$idx][$k]['breadth'] = 
					$areatitles[$areas[$_POST[$idx][$k]['course']]['area']];
			}
		}
	}

	// normalize the areas in the transfer section
	// handle 'transfer' separately because of extra popup
	if (isset($_POST['transfer'])) {
		foreach($_POST['transfer'] as $k=>$v) {
			$_POST['transfer'][$k]['term'] = normalize_term($_POST['transfer'][$k]['term']);
			$_POST['transfer'][$k]['course'] = normalize_course($_POST['transfer'][$k]['course']);
			$_POST['transfer'][$k]['title'] = get_course_title('transfer', $_POST['transfer'][$k]['course']);
			// we have N areas, so we count from 0-N
			$n = count($areatitles);
			for ($a = 0; $a < $n; $a++)
				$_POST['transfer'][$k]['area'.$a] = ($_POST['transfer'][$k]['breadth'] == $a ? "selected":"");
		}
	}

	// normalize seminar term and admissions term
	$_POST['seminar1'] = normalize_term($_POST['seminar1']);
	$_POST['seminar2'] = normalize_term($_POST['seminar2']);
	$_POST['seminar3'] = normalize_term($_POST['seminar3']);
	$_POST['admission'] = normalize_term($_POST['admission']);

	// normalize the research credits and term
	if (isset($_POST['research'])) {
		foreach($_POST['research'] as $k=>$v) {
			$_POST['research'][$k]['course'] = normalize_course(RESEARCH_COURSE);
			$_POST['research'][$k]['term'] = normalize_term($_POST['research'][$k]['term']);
		}
	}

	// normalize the select choice (faculty) options
	if (isset($_POST['committee'])) {
		foreach($_POST['committee'] as $k=>$v) {
			$_POST['committee'][$k]['profname'] = ucwords(strtolower($_POST['committee'][$k]['profname']));
			// $r = strtolower($_POST['committee'][$k]['role']);
			// $_POST['committee'][$k][$r] = true;
			// we have 4 roles (chair, co-chair, member, outside),
			// so we count from 1-4
			for ($a = 1; $a < 5; $a++)	// @@ committee size
				$_POST['committee'][$k]['role'.$a] = ($_POST['committee'][$k]['role'] == $a ? "selected":"");
		}
	}


	// Lets accumulate all courses into a table of the form:
	// ['course'], ['section'], ['term'], ['credits']
	$courses = array();
	$totalcr = 0;
	foreach ($courseGroups as $idx) {
		if (isset($_POST[$idx])) {
			foreach($_POST[$idx] as $k=>$v) {
				if (isset($areas[$_POST[$idx][$k]['course']])) {
					$bidx = $areas[$_POST[$idx][$k]['course']]['area'];
					$b = $areatitles[$bidx];
				}
				else {
					$b = "";
					$bidx = -1;
				}
				$courses[] = array(
					'course'=> $_POST[$idx][$k]['course'],
					'term'=> $_POST[$idx][$k]['term'],
					'credits'=> $_POST[$idx][$k]['credits'],
					'section' => strtolower($idx),
					'breadth' => $bidx,
					'breadthtitle' => $b,
					'limit'=>isset($areas[$_POST[$idx][$k]['course']]['limit'])?	// if set...
						$areas[$_POST[$idx][$k]['course']]['limit'] : 1);	// else limit is once
				$totalcr += $_POST[$idx][$k]['credits'];
			}
		}
	}
	// Process ['transfer'] separately... the value of the area is coming back from
	// the HTML directly in an index to areatitles
	if (isset($_POST['transfer'])) {
		foreach($_POST['transfer'] as $k=>$v) {
			$bidx = $_POST['transfer'][$k]['breadth'];
			$b = $areatitles[$bidx];
			$courses[] = array(
				'course'=> $_POST['transfer'][$k]['course'],
				'term'=> $_POST['transfer'][$k]['term'],
				'credits'=> $_POST['transfer'][$k]['credits'],
				'section' => 'transfer',
				'breadth' => $bidx,
				'breadthtitle' => $b);
			$totalcr += $_POST['transfer'][$k]['credits'];
		}
	}
	// Process ['research'] credits separately
	if (isset($_POST['research'])) {
		$research_hours = 0;
		foreach($_POST['research'] as $k=>$v) {
			$courses[] = array(
				'course'=> RESEARCH_COURSE,	// ms thesis or ind. study credit
				'term'=> $_POST['research'][$k]['term'],
				'credits'=> $_POST['research'][$k]['credits'],
				'section' => 'research',
				'breadth' => -1);
			 $research_hours += $_POST['research'][$k]['credits'];
		}
		if ($research_hours > RESEARCH_MAX_CR)
			$research_hours = RESEARCH_MAX_CR;		// only this max will count towards degree
		$totalcr += $research_hours;
	}
	// add seminars to courses here... @@ why? so we can print them later?
	// We now have all the courses into a single array... with all the
	// data normalized and cleaned up...

	// print_r($courses);

	// Error check and messages
	// $_POST['messages'] = array();

	if (strlen(trim($_POST['name'])) == 0)
		$_POST['top_messages'][] = array('message'=>"Please enter your full name.");

	if (strlen(trim($_POST['studentid'])) == 0)
		$_POST['top_messages'][] = array('message'=>"Please enter your student id.");

	// valid format for studentid
	if (!valid_studentid(trim($_POST['studentid'])))
		$_POST['top_messages'][] = array('message'=>"Your student id must have 9 digits.");

	if (strlen(trim($_POST['pid'])) == 0)
		$_POST['top_messages'][] = array('message'=>"Please enter your PID.");

	if (strlen(trim($_POST['admission'])) == 0)
		$_POST['top_messages'][] = array('message'=>"Please enter the semester when you were admitted into the ".DEGREE_NAME." program.");

	// valid term/year
	if (!valid_term($_POST['admission']))
		$_POST['top_messages'][] = array('message'=>"Please enter a valid term (semester/year) for admissions in the form of Fall/Spring/Summer followed by the year (4 digits). Ex: Fall 2012, Spring 2013.");

	if (!valid_term($_POST['seminar1']))
		$_POST['seminar_messages'][] = array('message'=>"Please enter a valid term (semester/year) for your first seminar in the form of Fall/Spring/Summer followed by the year (4 digits). Ex: Fall 2012, Spring 2013.");
	if (!valid_term($_POST['seminar2']))
		$_POST['seminar_messages'][] = array('message'=>"Please enter a valid term (semester/year) for your second seminar in the form of Fall/Spring/Summer followed by the year (4 digits). Ex: Fall 2012, Spring 2013.");
	if (!valid_term($_POST['seminar3']))
		$_POST['seminar_messages'][] = array('message'=>"Please enter a valid term (semester/year) for your third seminar in the form of Fall/Spring/Summer followed by the year (4 digits). Ex: Fall 2012, Spring 2013.");

	$courseGroups = array('senior', 'fivethousand', 'sixthousand', 'cognate', 'transfer', 'research');
	foreach ($courseGroups as $idx) {
		if (isset($_POST[$idx])) {
			$skip = false;	// process only one course per section
			foreach($_POST[$idx] as $k=>$v) {
				if (strlen(trim($v['term'])) == 0) {
					$_POST[$idx.'_messages'][] = 
						array('message'=>"Term (semester/year) is blank in one of the courses in this section.");
					$skip = true;
				}
				else if (!valid_term($v['term'])) {
					$_POST[$idx.'_messages'][] = 
						array('message'=>"Please enter a valid term (semester/year) for the courses in this section in the form of Fall/Spring/Summer followed by the year (4 digits). Ex: Fall 2012, Spring 2013.");
					$skip = true;
				}

				if (!valid_credithours($v['credits'])) {
					$_POST[$idx.'_messages'][] = 
						array('message'=>"The credits for a course must be an integer greater than 0.");
					$skip = true;
				}

				if (!valid_coursename($v['course'])) {
					$_POST[$idx.'_messages'][] = 
						array('message'=>"The course name ({$v['course']}) must include the department abbreviation (e.g., CS) and the course number (e.g., 5774).");
					$skip = true;
				}

				if ($skip) break;	// process only one course per section
			}
		}
	}


	if (!valid_transfers($courses, TRANSFER_LIMIT_CR))
		$_POST['transfer_messages'][] = array('message'=>"You may transfer up to ".TRANSFER_LIMIT_CR." credits of courses in your ".DEGREE_NAME." plan of study.");

	if (!valid_cognates($courses, COGNATE_MIN_CR, COGNATE_MAX_CR))
		$_POST['cognate_messages'][] = array('message'=>"You need between ".COGNATE_MIN_CR." and ".COGNATE_MAX_CR." credits of approved cognate courses to satisfy the ".DEGREE_NAME." degree requirements.");

	if (!valid_cognates_courses($courses))
		$_POST['cognate_messages'][] = array('message'=>"At least one of our cognate courses is not on the approved list.");

	if (!valid_senior($courses, SENIOR_MAX_CR))
		$_POST['senior_messages'][] = array('message'=>"You can have at most ".SENIOR_MAX_CR." credits in CS 4xxx in your plan of study.");

	if (!valid_senior_course($courses))
		$_POST['senior_messages'][] = array('message'=>"You can have a CS 4xxx course in your plan of study that is not on the approved list of courses.");

	if (!valid_sixthousand($courses, SIXTHOUSAND_MIN_CR))
		$_POST['sixthousand_messages'][] = array('message'=>"You need at least ".SIXTHOUSAND_MIN_CR." credits of CS 6xxx courses to satisfy the ".DEGREE_NAME." degree requirements.");

	// if (!only_one_5974($courses))
	// 	$_POST['fivethousand_messages'][] = array('message'=>"You can have only 1 course CS 5974 course in your plan of study.");

	if ($_POST['msthesis'] == 1) { // thesis
		if (!valid_research_hrs($courses, RESEARCH_MIN_CR))//, RESEARCH_MAX_CR))
			$_POST['research_messages'][] = array('message'=>"You need at least ".RESEARCH_MIN_CR." credits of ".RESEARCH_COURSE." to satisfy the ".DEGREE_NAME." degree requirements.");
	}
	else {
		if (!valid_limits($courses, IND_STUDY_MIN_CR, IND_STUDY_MAX_CR))
			$_POST['research_messages'][] = array('message'=>"You can only use between ".IND_STUDY_MIN_CR." and ".IND_STUDY_MAX_CR." credits of Independent Study to satisfy the ".DEGREE_NAME." degree requirements.");
	}

	if ($totalcr < DEGREE_MIN_CR) {
		$_POST['totalcr_messages'][] = array('message'=>"You need a minimum of ".DEGREE_MIN_CR." credits to complete your ".DEGREE_NAME.". Currently you only have $totalcr credits.");
	}
	else {
		$_POST['creditfeedback'] = "Your plan of study meets the minimum number credits to complete your ".DEGREE_NAME.".";
	}


	// @@ one more check... the terms for the graduate seminar cannot be repeated
	// @@ collect the name for the 6000 courses (particularly the TS:)

	// breadth requirement... using the accumulated group of 
	// courses done above... and while we are at it, check
	// for courses that appear twice

	$double = array();		// check for doubly counted courses
	$breadth = array();
	foreach($courses as $course) {
		// If the course is a research course, then it
		// can appear twice in the plan of study and cannot
		// be used for breadth requirements...
		if ($course['course'] == RESEARCH_COURSE)
			continue;

		// now... if the course has not been used yet...
		if (!isset($double[$course['course']])) {
			$double[$course['course']] = 1;

			// if we haven't seen this course before, then use it
			// for the breadth requirement, IF ALLOWED
			if ($course['breadth'] >= 0) {	// count it if breadth is >= 0, -1 is used to discount breadth
				if (!isset($breadth[$course['breadth']]))
					$breadth[$course['breadth']] = $course['course'];
			}
		}
		else if ((isset($course['limit'])) && ($course['limit'] > 1)) {
			$double[$course['course']]++;		// keep track of how many times
			// but we have used it before, so it will not count for breadth
		}
		else {
			$_POST['top_messages'][] = array('message'=>"Course {$course['course']} appears twice in the plan of study. One course cannot count for two requirements.");
		}
	}

	// now, do we have BREADTH_COURSES areas?
	$breadthCount = count($breadth);
	if ($breadthCount == 0)
		$_POST['top_messages'][] = array('message'=>"You need to cover ".BREADTH_COURSES." different areas (breadth).");
	else if ($breadthCount < (BREADTH_COURSES-1))
		$_POST['top_messages'][] = array('message'=>"You need to cover ".BREADTH_COURSES." different areas (breadth), but you only have $breadthCount.");

	// Breadth coverage... put only the first BREADTH_COURSES
	$i = 0;
	foreach($breadth as $k=>$c) {
		$_POST['breadth_messages'][] = array('message'=>"$i: $c " . $areatitles[$k]);
		$i++;
		if ($i > (BREADTH_COURSES - 1))
			break;
	}

	// Send this back for the form
	$_POST['director'] = $director;
	$_POST['depthead'] = $depthead;
	$_POST['coordinator'] = $coordinator;
	$_POST['printed'] = date("m/d/Y");

	// We end by generating either a print or the same page
	// print_r($_POST);
	if ($_POST['submit'] == "Print") {
		$_POST['comments'] = nl2br($_POST['comments']);
		echo gen_template("print.tmpl", $_POST);
	}
	else if ($_POST['submit'] == "Validate")
		echo gen_template("ms.tmpl", $_POST);
	else {	// submit == Download
		// print_r($_POST);
		// $j = json_encode($_POST, JSON_PRETTY_PRINT);
		$j = json_encode($_POST);//, JSON_PRETTY_PRINT);
		header("Content-Disposition: attachment; filename=\"".$_POST['pid'].".pos\";");
		header("Content-Type: text/pos; charset=utf-8");
	    header('Content-Length: ' . strlen($j));
	    ob_clean();
	    flush();
	    echo $j;
	}
}


// else, create a fake blank $_POST (or an array with same structure)
else {
	$val = array();
	$vars = get_template_vars("ms.tmpl");
	foreach($vars as $k)
		$val[$k] = "";

	if (isset($_GET['course'])) {
		// set the coursework degree
		$val['msthesis'] = 0;
		$val['mscoursework'] = 1;
		// fix the committee to just 1 member
		$val['committee'][0]['role1'] = "selected";
		define(RESEARCH_COURSE, "CS5974");
		define(RESEARCH_COURSE_TITLE, "CS5974 Independent Study");
	}
	else {
		// set the default degree
		$val['msthesis'] = 1;
		$val['mscoursework'] = 0;
		// fix the committee to be 3 members and set default roles
		$val['committee'][0]['role1'] = "selected";
		$val['committee'][1]['role3'] = "selected";
		$val['committee'][2]['role3'] = "selected";
		define(RESEARCH_COURSE, "CS5994");
		define(RESEARCH_COURSE_TITLE, "CS5994 Research and Thesis");
	}
	
	$val['research'][] = array(
		'term'=>"", 
		'course' => RESEARCH_COURSE,
		'title' => RESEARCH_COURSE_TITLE,
		'section' => 'research',
		'breadth'=> -1);

	$val['director'] = $director;
	$val['depthead'] = $depthead;
	$val['coordinator'] = $coordinator;
	$val['printed'] = date("m/d/Y");

	echo gen_template("ms.tmpl", $val);
}
?>