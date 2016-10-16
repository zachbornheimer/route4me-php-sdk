<?php
	namespace Route4Me;
	
	$vdir=$_SERVER['DOCUMENT_ROOT'].'/route4me/examples/';

    require $vdir.'/../vendor/autoload.php';
	
	use Route4Me\Route4Me;
	use Route4Me\Member;
	
	// Example refers to getting of an user with details.
	
	// Set the api key in the Route4me class
	Route4Me::setApiKey('11111111111111111111111111111111');
	
	$param = array (
		"member_id" => 45844
	);
	
	$member = new Member();
	
	$response = $member->getUser($param);
	
	Route4Me::simplePrint($response);
	
?>