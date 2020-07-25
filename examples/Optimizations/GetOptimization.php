<?php

namespace Route4Me;

$root = realpath(dirname(__FILE__).'/../../');
require $root.'/vendor/autoload.php';

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_BAIL, 1);

// Set the api key in the Route4Me class
Route4Me::setApiKey(Constants::API_KEY);

// Get random optimization problem ID
$optimization = new OptimizationProblem();

$optimizationProblemId = $optimization->getRandomOptimizationId(0, 10);

assert(!is_null($optimizationProblemId), "Cannot retrieve a random optimization problem ID");

// Get an optimization problem
$optimizationProblemParams = [
    'optimization_problem_id' => $optimizationProblemId,
];

$optimizationProblem = $optimization->get($optimizationProblemParams);

foreach ((array) $optimizationProblem as $probParts) {
    Route4Me::simplePrint((array) $probParts);
}
