<?php
// File: library.php
// Description: Library of utility routines used in the Plan of Study code.
// Author: mapq
//

// Function: endsWith
// Description: simple string check, borrowed from stack overflow
// Params:

function endsWith($haystack, $needle)
{
    // search forward starting from end minus needle length characters
    if ($needle === "")
    	return true;
    else {
    	$temp = strlen($haystack) - strlen($needle);
	    return ($temp >= 0) && (strpos($haystack, $needle, $temp) !== FALSE);
	}
}


// Function: get_course_title
// Description: Given a course number, return its title

function get_course_title($area, $course)
{
	global $cognates, $areas, $senior;
	if ($area == "fivethousand")
		return $areas[$course]['title'];
	else if ($area == "sixthousand")
		return $areas[$course]['title'];
	else if ($area == "cognate")
		return $cognates[$course];
	else if ($area == "senior")
		return $senior[$course];
	else
		return "--";
}

// Function: normalize_course
// Description: Normalize the course number to the format of CS4588
// Params: $course

function normalize_course($course)
{
	// strip anything other than letters and numbers resulting in
	// a course in the format of CS4588
	return strtoupper(preg_replace("/([^a-zA-Z0-9])/", "", trim($course)));
}

// Function: normalize_term
// Description: 
// Params:

function normalize_term($term)
{
	// strip spaces and leave the term and the year... summer must be
	// Summer not Summer I or II
	$term = ucwords(strtolower(trim(strtok($term, " \t"))));
	$year = strtok(" \t");
	if (!$year) {
		// possibly there is no space between term and year
		preg_match("/([a-zA-Z]+)[ ]*([0-9]+)/", trim($term), $match);
		$term = ucwords(strtolower($match[1]));
		$year = $match[2];
		// echo "$term<br>";//print_r($match);
		return trim($term." ".$year);
	}
	else if (strlen($term.$year) > 0)
		return trim($term." ".$year);
	else
		return "";
}

// Function: valid_studentid
// Description: 
// Params:

function valid_studentid($id)
{
	// strip anything other than numbers
	$id = preg_replace("/([^0-9])/", "", $id);

	// strlen=9 -- must be is_numeric()
	return (strlen($id) == 9);
}

// Function: valid_coursename
// Description: 
// Params:

function valid_coursename($c)
{
	if (strlen(trim($c)) < 5)	// must be at least 5 chars
		return false;

	preg_match("/^([a-zA-Z]+)([0-9]+)$/", trim($c), $match);

	$dept = strtoupper($match[1]);
	$number = $match[2];
	return (ctype_alpha($dept) && is_numeric($number));
}

// Function: valid_coursexx
// Description: 
// Params:

function valid_coursexx($c)
{
	global $cognates, $areas, $senior;
	if (strlen(trim($c)) < 6)	// must be at least 6 chars
		return false;

	preg_match("/^([a-zA-Z]+)([0-9]+)$/", trim($c), $match);

	$dept = strtoupper($match[1]);
	$number = $match[2];
	$name = $dept.$number;

	// check to see if it is in array
	if (isset($areas[$name]))
		return true;
	else if (isset($senior[$name]))
		return true;
	else if (isset($cognates[$name]))
		return true;
	else
		return false;
}

// Function: valid_credithours
// Description: 
// Params:

function valid_credithours($h)
{
	if (is_numeric($h) && (intval($h)>0))
		return true;
	else
		return false;
}

// Function: valid_term
// Description: 
// Params:

function valid_term($t)
{
	if (strlen(trim($t)) < strlen("fall 9999"))	// must be at least this long
		return false;

	$term = strtolower(trim(strtok($t, " \t")));
	$year = strtok(" \t");
	if ((strcmp($term, "fall") == 0) ||
		(strcmp($term, "spring") == 0) ||
		(strcmp($term, "summer") == 0))
		if (is_numeric($year) && (strlen($year) == 4))
			return true;
	return false;
}

// Function: valid_transfers
// Description: 
// Params:

function valid_transfers($courses, $limit)
{
	$total = 0;
	foreach($courses as $course) {
		if ($course['section'] == "transfer") {
			$total += $course['credits'];
		}
	}
	return $total <= $limit;
}

// Function: valid_indepedent_study_hrs
// Description: 
// Params:

function valid_indepedent_study_hrs($courses, $min, $max)
{
	$total = 0;
	foreach($courses as $course) {
		if ($course['section'] == "research")
			$total += $course['credits'];
	}
	return $total >= $min && $total <= $max;
}

// Function: valid_research_hrs
// Description: 
// Params:

function valid_research_hrs($courses, $min)
{
	$total = 0;
	foreach($courses as $course) {
		if ($course['section'] == "research")
			$total += $course['credits'];
	}
	return $total >= $min;	// && $total <= $max;
}

// Function: valid_cognates
// Description: 
// Params:

function valid_cognates($courses, $min, $max)
{
	$total = 0;
	foreach($courses as $course) {
		if ($course['section'] == "cognate") {
			$total += $course['credits'];
		}
	}
	return $total >= $min && $total <= $max;
}

// Function: valid_cognates_courses
// Description: 
// Params:

function valid_cognates_courses($courses)
{
	global $cognates;
	foreach($courses as $course) {
		if ($course['section'] == "cognates") {
			// check that the cognate is in the valid list
			if (!isset($cognates[$course['course']]))
				return false;
		}
	}
	return true;
}

// Function: valid_senior
// Description: 
// Params:

function valid_senior($courses, $limit)
{
	$total = 0;
	foreach($courses as $course) {
		if ($course['section'] == "senior")
			$total += $course['credits'];
	}
	return $total <= $limit;
}

// Function: valid_senior_course
// Description: 
// Params:

function valid_senior_course($courses)
{
	global $senior;
	$total = 0;
	foreach($courses as $course) {
		if ($course['section'] == "senior")
			// check that the course is in the valid list of senior courses
			if (!isset($senior[$course['course']]))
				return false;
	}
	return true;
}

// Function: only_one_5974
// Description: 
// Params:

function only_one_5974($courses)
{
	$totalcr = 0;
	foreach($courses as $course) {
		if (($course['section'] == "fivethousand") &&
			($course['course']) == "CS5974")
			$totalcr += $course['credits'];
	}
	return $totalcr <= 3;
}

// Function: valid_sixthousand
// Description: 
// Params:

function valid_sixthousand($courses, $min)
{
	$totalcr = 0;
	foreach($courses as $course) {
		if (strtolower($course['section']) == "sixthousand")
			$totalcr += $course['credits'];
	}
	return $totalcr >= $min;
}

?>