<?php

// colorize the output of the test
require_once __DIR__.'/../colors.php';

use Colors\Color;
$colors = new Color;

// avoid declaring the function twice (or more), when calling several tests at once
if (!function_exists('percent')) {
    /**
     * percent 
     * 
     * @param mixed $suiteResults 
     * @access public
     * @return void
     */
    function percent($suiteResults) {
        $sum = $suiteResults['pass'] + $suiteResults['fail'];
        return $sum > 0 ? round($suiteResults['pass'] * 100 / $sum, 2) : 100;
    }
}

$result = $suiteResults['fail'] === 0 ? 'pass' : 'fail';
$resultColored = strcasecmp($result, 'pass') == 0 ? $colors($result)->green : $colors($result)->red;

echo str_repeat('-', 80)."\n",
    " $title  [$resultColored]\n";

foreach($cases as $caseTitle => $case) {
	$resultColored = strcasecmp($result, 'pass') == 0 ? $colors($result)->green() : $colors($result)->red();
    echo "\n",
        str_repeat('-', 80)."\n",
        "[".$resultColored."]  $caseTitle  {pass {$case['pass']} / fail {$case['fail']}}\n\n";

    foreach ($case['tests'] as $test) {
		$testResultColorized = strcasecmp($test['result'], 'pass') == 0 ? 
			$colors($test['result'])->green() : $colors($test['result'])->red();
        echo "[{$testResultColorized}] {$test['type']}()\n",
            str_repeat(' ', 7)."line {$test['line']}, {$test['file']}\n",
            str_repeat(' ', 7)."{$test['source']}\n";

        // output the message in case of failure
        if (strcasecmp($test['result'], 'fail') == 0 && isset($test['name']) && strlen($test['name']) > 0) {
            echo str_repeat(' ', 7)."{$test['name']}\n";
        }
    }
}

// colorize the output
$outputColor = strcasecmp($result, 'pass') == 0 ? 'green' : 'red';

// output total test results
echo str_repeat('=', 80)."\n";
echo $colors("Tests: [$result], ".
    "{pass {$suiteResults['pass']} / fail {$suiteResults['fail']}}, ".
    percent($suiteResults)."% success\n")->$outputColor();

