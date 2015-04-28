<?php
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

function normalize_course($course)
{
	// strip anything other than letters and numbers resulting in
	// a course in the format of CS4588
	return strtoupper(preg_replace("/([^a-zA-Z0-9])/", "", trim($course)));
}

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

function valid_studentid($id)
{
	// strip anything other than numbers
	$id = preg_replace("/([^0-9])/", "", $id);

	// strlen=9 -- must be is_numeric()
	return (strlen($id) == 9);
}

function valid_coursename($c)
{
	if (strlen(trim($c)) < 5)	// must be at least 5 chars
		return false;

	preg_match("/^([a-zA-Z]+)([0-9]+)$/", trim($c), $match);

	$dept = strtoupper($match[1]);
	$number = $match[2];
	return (ctype_alpha($dept) && is_numeric($number));
}

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

function valid_credithours($h)
{
	if (is_numeric($h) && (intval($h)>0))
		return true;
	else
		return false;
}

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

function valid_limits($courses, $min, $max)
{
	$total = 0;
	foreach($courses as $course) {
		if ($course['section'] == "research")
			$total += $course['credits'];
	}
	return $total >= $min && $total <= $max;
}

function valid_research_hrs($courses, $min)
{
	$total = 0;
	foreach($courses as $course) {
		if ($course['section'] == "research")
			$total += $course['credits'];
	}
	return $total >= $min;	// && $total <= $max;
}

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

function valid_senior($courses, $limit)
{
	$total = 0;
	foreach($courses as $course) {
		if ($course['section'] == "senior")
			$total += $course['credits'];
	}
	return $total <= $limit;
}

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