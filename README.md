Testify - a micro unit testing framework
========================================
Testify is a micro unit testing framework for PHP 5.3+. It strives for elegance instead of feature bloat. Testing your code is no longer a chore - it's fun again.

## Requirements

* PHP 5.3+ is required
* [Composer](http://getcomposer.org/) to install Testify is recommended (but you can do this manually)


Usage
-----
Here is an example for a test suite with two test cases:

```php
require 'vendor/autoload.php';

use Math\MyCalc;
use Testify\Testify;

$tf = new Testify("MyCalc Test Suite");

$tf->beforeEach(function($tf) {
	$tf->data->calc = new MyCalc(10);
});

$tf->test("Testing the add() method", function($tf) {
	$calc = $tf->data->calc;

	$calc->add(4);
	$tf->assert($calc->result() == 14);

	$calc->add(-6);
	$tf->assertEquals($calc->result(), 8);
});

$tf->test("Testing the mul() method", function($tf) {
	$calc = $tf->data->calc;

	$calc->mul(1.5);
	$tf->assertEquals($calc->result(), 12);

	$calc->mul(-1);
	$tf->assertEquals($calc->result(), -12);
});

$tf();
```

RESTFul tests extension (for testing RESTFul APIs)
(The library uses php CURL module)

Usage:

```php
$tfr = new \Testify\Testify_Restful;

// assert restful service
$tfr->assertRequest($method, $url, $params, $expectedResultOrMethod, $message = '')
// assert all the requests in the CSV file
$tfr->assertCSV($CSV_fileName);

// example #1: check result for exact output
$tfr->assertRequest($method = 'POST', $url = 'http://example.com/users', $params = array('username'=>'sgchris'), 
  $expectedResult = '{"result":"ok"}');

// example #2: check result with callback
$tfr->assertRequest($method = 'POST', $url = 'http://example.com/users', $params = array('username'=>'sgchris'), function($result) {
  // manipulate/check the response and return boolean value
  return !empty($result) && strlen($result) == 10;
});

// example #3: check CSV file
// CSV file format:
// | method (string - get/post/put/..) | url (string) | parameters (query string) | expected result (string) |
$tfr->assertCSV('requests_descriptions.csv');

// execute the test
$tfr();
```

# Documentation

 * `__construct( string $title )` - The constructor
 * `test( string $name, [Closure $testCase = null] )` - Add a test case.
 * `before( Closure $callback )` - Executed once before the test cases are run
 * `after( Closure $callback )` - Executed once after the test cases are run
 * `beforeEach( Closure $callback )` - Executed for every test case, before it is run
 * `afterEach( Closure $callback )` - Executed for every test case, after it is run
 * `run( )` - Run all the tests and before / after functions. Calls report() to generate the HTML report page
 * `assert( boolean $arg, [string $message = ''] )` - Alias for assertTrue() method
 * `assertTrue( boolean $arg, [string $message = ''] )` - Passes if given a truthfull expression
 * `assertFalse( boolean $arg, [string $message = ''] )` - Passes if given a falsy expression
 * `assertEquals( mixed $arg1, mixed $arg2, string [string $message = ''] )` - Passes if $arg1 == $arg2
 * `assertNotEquals( mixed $arg1, mixed $arg2, string [string $message = ''] )` - Passes if $arg1 != $arg2
 * `assertSame( mixed $arg1, mixed $arg2, string [string $message = ''] )` - Passes if $arg1 === $arg2
 * `assertNotSame( mixed $arg1, mixed $arg2, string [string $message = ''] )` - Passes if $arg1 !== $arg2
 * `assertInArray( mixed $arg, array $arr, string [string $message = ''] )` - Passes if $arg is an element of $arr
 * `assertNotInArray( mixed $arg, array $arr, string [string $message = ''] )` - Passes if $arg is not an element of $arr
 * `pass( string [string $message = ''] )` - Unconditional pass
 * `fail( string [string $message = ''] )` - Unconditional fail
 * `report( )` - Generates a pretty CLI or HTML5 report of the test suite status. Called implicitly by run()
 * `__invoke( )` - Alias for run() method

# RESTFul extension
 * `assertRequest($method, $url, $params, $expectedResultOrMethod[, $message = '']) - execute a RESTFul call and compare the result / execute callback
 * `assertCSV($CSV_fileName) - execute all the requests in the CSV

