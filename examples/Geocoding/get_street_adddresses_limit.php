<?php
namespace Route4Me;

$root = realpath(dirname(__FILE__).'/../../');
require $root.'/vendor/autoload.php';

use Route4Me\Route4Me;
use Route4Me\Geocoding;

// Example refers to getting limited number geocodings.

// Set the api key in the Route4me class
Route4Me::setApiKey('11111111111111111111111111111111');

$gcParameters = (array)Geocoding::fromArray(array(
    "offset" => 0,
    "limit"  => 5
));

$geocoding = new Geocoding();

$response = $geocoding->getStreetData($gcParameters);

foreach ($response as $gcode) {
    Route4Me::simplePrint($gcode);
    echo "<br>";
}
