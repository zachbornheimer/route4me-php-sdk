<?php

namespace Route4Me;

$root = realpath(dirname(__FILE__).'/../../');
require $root.'/vendor/autoload.php';

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_BAIL, 1);

// Set the api key in the Route4me class
Route4Me::setApiKey('11111111111111111111111111111111');

$track = new Track();

$userLocation = reset($track->getUserLocations());

$email = $userLocation['member_data']['member_email'];
$queriedUserLocations = $track->getUserLocations($email);

foreach ($queriedUserLocations As $memberId => $userLocation) {
    echo $userLocation['member_data']['member_first_name'].' '.$userLocation['member_data']['member_last_name']." --> ";
    if (isset($userLocation['tracking']['position_lng'])) {
        echo "Longitude: ".$userLocation['tracking']['position_lng'].", Latitude: ".$userLocation['tracking']['position_lat'];
    }
    echo "<br>";
}
