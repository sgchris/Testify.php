<?php

// check input parameters
if ($_SERVER['argc'] < 2) {
    echo 'No parameters supplied', "\n";
    echo 'format: ', "\n";
    echo '# php Testify.php MyTests/myTest1 MyTests/myTest2', "\n";
    exit();
}

// include the libraries
require_once  __DIR__.'/Testify/Testify.php';
require_once  __DIR__.'/Testify/Testify_Restful.php';

// get all the tests
$tests = array_slice($_SERVER['argv'], 1);

if (!empty($tests)) {
    foreach ($tests as $test) {

        // remove the extension
        $test = preg_replace('%\.php$%i', '', $test);

        if (file_exists(__DIR__.'/'.$test.'.php')) {
            require __DIR__.'/'.$test.'.php';
        } else {
            echo "File {$test} does not exist\n";
        }
    }
}
