<?php

$baseUrl = 'http://api.snipeasy.com';

$t = new \Testify\Testify_Restful('Test snippets API');

$t->test('Test RESTFul API', function($t) {
    global $baseUrl;

    $csvFiles = glob(__DIR__.'/*.csv');
    if (!empty($csvFiles)) {
        foreach ($csvFiles as $file) {
            
        }
    }

    $t->assertCSV(__DIR__.'/snippets.csv');

});


$t();
