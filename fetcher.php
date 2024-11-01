<?php

function date3339($timestamp=0) {

	if (!$timestamp) {
		$timestamp = time();
	}
	$date = date('Y-m-d\TH:i:s', $timestamp);
	
	$matches = array();
	if (preg_match('/^([\-+])(\d{2})(\d{2})$/', date('O', $timestamp), $matches)) {
		$date .= $matches[1].$matches[2].':'.$matches[3];
	} else {
		$date .= 'Z';
	}
	return $date;
	
}

if	(
	preg_match('/^\w+@[\w\.]+\w{2,5}$/i', $_GET['c'])
	&&
	preg_match('/^\d{1,2}$/', $_GET['hmd'])
	&&
	preg_match('/^\d{1,2}$/', $_GET['mr'])
	) {
	$theURL = $_GET['c'];
	$startMin = date3339(strtotime('today'));
	$startMax = date3339(strtotime('today') + ($_GET['hmd'] * (60*60*24)));
	
	$theNewURL  = 'http://www.google.com/calendar/feeds/';
	$theNewURL .= $_GET['c'];
	$theNewURL .= '/public/full';
	$theNewURL .= '?singleevents=true';
	$theNewURL .= '&orderby=starttime';
	$theNewURL .= '&sortorder=ascending';
	$theNewURL .= '&start-min='.$startMin;
	$theNewURL .= '&start-max='.$startMax;
	$theNewURL .= '&max-results='.$_GET['mr'];

	$theCal = file_get_contents($theNewURL);

	header ("content-type: text/xml");
	echo ($theCal);

} else {

	die ("FAIL: didn't get what I'd expected");

}

?>