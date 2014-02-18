<?php
// avoid declaring the function twice (or more), when calling several tests at once
if (!function_exists('percent')) {
	function percent($suiteResults) {
		$sum = $suiteResults['pass'] + $suiteResults['fail'];
		return $sum > 0 ? round($suiteResults['pass'] * 100 / $sum, 2) : 100;
	}
}

$result = $suiteResults['fail'] === 0 ? 'pass' : 'fail';

echo str_repeat('-', 80)."\n",
    " $title  [$result]\n";

foreach($cases as $caseTitle => $case) {
    echo "\n",
        str_repeat('-', 80)."\n",
        "[$result]  $caseTitle  {pass {$case['pass']} / fail {$case['fail']}}\n\n";

    foreach ($case['tests'] as $test) {
        echo "[{$test['result']}] {$test['type']}()\n",
            str_repeat(' ', 7)."line {$test['line']}, {$test['file']}\n",
            str_repeat(' ', 7)."{$test['source']}\n";

        // output the message in case of failure
        if (strcasecmp($test['result'], 'fail') == 0 && isset($test['name']) && strlen($test['name']) > 0) {
            echo str_repeat(' ', 7)."{$test['name']}\n";
        }
    }
}

echo str_repeat('=', 80)."\n",
    "Tests: [$result], {pass {$suiteResults['pass']} / fail {$suiteResults['fail']}}, ",
    percent($suiteResults)."% success\n";

