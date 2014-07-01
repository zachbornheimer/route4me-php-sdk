<?php
require __DIR__.'/../vendor/autoload.php';;

use Route4me\Enum\DeviceType;
use Route4me\Enum\Format;
use Route4me\TrackSetParams;
use Route4me\Track;

$params = TrackSetParams::fromArray(array(
    'format'           => Format::CSV,
    'route_id'         => 'AC16E7D338B551013FF34266FE81A5EE',
    'member_id'        => 1,
    'course'           => 1,
    'speed'            => 120,
    'lat'              => 41.8927521,
    'lng'              => -109.0803888,
    'device_type'      => DeviceType::IPHONE,
    'device_guid'      => 'qweqweqwe',
    'device_timestamp' => date('Y-m-d H:i:s')
));

$status = Track::set($params);

var_dump($status);
