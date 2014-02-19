<?php

/**
 * Example using tests of a RESTFul API
 */

// initilize the autoloader
require '../vendor/autoload.php';

// use the namespace
use Testify\Testify_Restful;

// initialize the RESTFul test class
$t = new \Testify\Testify_Restful('Test snippets API');

// example of a test
$t->test('Test RESTFul API', function($t) {

	// get all the CSV file in the current directory
    $csvFiles = glob(__DIR__.'/*.csv');

	// loop the files and assert one by one
    if (!empty($csvFiles)) {
        foreach ($csvFiles as $file) {
			$t->assertCSV(__DIR__.'/'.$file);
        }
    }

});

// execute the test
$t();
