<?php
/**
 * Extension of the main Testify file for testing RESTFul APIs
 * using direct input (array parameter) or a CSV file
 * The library uses php CURL
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
 * Usage:
 *
 * $tfr = new \Testify\Testify_Restful;
 * 
 * // assert restful service
 * $tfr->assertRequest($method, $url, $params, $expectedResultOrMethod, $message = '')
 * // assert all the requests in the CSV file
 * $tfr->assertCSV($CSV_fileName);
 * 
 * // example #1: check result for exact output
 * $tfr->assertRequest($method = 'POST', $url = 'http://example.com/users', $params = array('username'=>'sgchris'), 
 *   $expectedResult = '{"result":"ok"}');
 * 
 * // example #2: check result with callback
 * $tfr->assertRequest($method = 'POST', $url = 'http://example.com/users', $params = array('username'=>'sgchris'), function($result) {
 *   // manipulate/check the response and return boolean value
 *   return !empty($result) && strlen($result) == 10;
 * });
 * 
 * // example #3: check CSV file
 * // CSV file format:
 * // | method (string - get/post/put/..) | url (string) | parameters (query string) | expected result (string) |
 * $tfr->assertCSV('requests_descriptions.csv');
 * 
 * // execute the test
 * $tfr();
 *
 * @author Gregory Chris <sgchris@gmail.com>
 * @url https://github.com/sgchris
 */
namespace Testify;

require_once __DIR__.'/Testify.php';

class Testify_Restful extends Testify {

    private $requestMethods = array('get', 'post', 'put', 'delete', 'head', 'options');
    protected $CSVFields = array('method', 'url', 'parameters', 'expected result');

    /**
     * Check if cURL module installed
     *
     * @access protected
     * @return bool
     */
    protected function CURLInstalled() { 
        return function_exists('curl_init') && function_exists('curl_setopt') && 
            function_exists('curl_exec') && function_exists('curl_close');
    } 


	/**
	 * extra headers on every call
	 * @var array
	 */
    private $headers = array();

    /**
     * @brief 
     * @param array $headers - e.g. array('Content-Type: text/html', ...)
     * @return \Testify_Restful
     */
    public function addCustomHeader($headers) {
        $this->headers = array_merge($this->headers, $headers);
		return $this;
    }

    /**
     * assertRequest 
     * 
     * @access public
     * @param string $method 
     * @param string $url 
     * @param mixed $parameters - string | array
     * @param string $expectedResult 
     * @param string $message 
     * @return bool
     */
    public function assertRequest($method, $url, $parameters, $expectedResultOrMethod, $message = '') {

        // initialize CURL
        if (!$this->CURLInstalled() || false === ($ch = @curl_init())) {
            return $this->recordTest(false, 'Error initializing CURL PHP module');
        }

        // prepare parameters for the request
        $parametersStr = is_array($parameters) && !empty($parameters) 
            ? http_build_query($parameters) 
            : (is_string($parameters) && !empty($parameters) ? $parameters : null);

        // set the request method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

        // set the request parameters
        if (!is_null($parametersStr)) {
			if (strcasecmp($method, 'put') == 0) {
				// content-length needed for PUT requests
				$this->addCustomHeader(array('Content-Length: ' . strlen($parametersStr)));
			}
			if (strcasecmp($method, 'post') == 0 || strcasecmp($method, 'put') == 0) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $parametersStr);
			} elseif (strcasecmp($method, 'get') == 0) {
				if (strpos($url, '?') !== false) {
					$url.= '&' . $parametersStr;
				} else {
					$url.= '?' . $parametersStr;
				}
			}
        }
		
        // set the URL
        curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers); 
		
        // set the return as string flag
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // execute the call
        $response = curl_exec($ch);

        // compare the result with the expected result
        if (is_string($expectedResultOrMethod)) {
            return $this->recordTest($response == $expectedResultOrMethod, "expected {$expectedResultOrMethod}, got {$response}\n{$message}");
        } elseif (is_callable($expectedResultOrMethod)) {
            $callbackResult = call_user_func_array($expectedResultOrMethod, array($response));
            return $this->recordTest($callbackResult, $message);
        } else {
            return $this->recordTest(false, $message);
        }

    }



    /**
     * assertCSV - check all the requests in a CSV file
     * 
     * @access public
     * @param mixed $CSVFileName 
     * @return void
     */
    public function assertCSV($CSVFile) {

        // load the data from the CSV file
        if (false === ($fp = fopen($CSVFile, 'r'))) {
            return $this->recordTest(false, 'Error loading "'.$CSVFile.'" file content');
        }
    
        // get the first line
        $row = fgetcsv($fp);

        // get the data from the file line by line
        while (false !== ($row = fgetcsv($fp))) {

			// check for empty rows
			if (isset($row[0]) && empty($row[0])) {
				continue;
			}

            // validate the row
            if (!$this->CSVRowIsValid($row)) {
				echo 'row ', json_encode($row), ' is invalid!', "\n";
                continue;
			}

            // assert the request
            $this->assertRequest( $row[array_search('method', $this->CSVFields)], $row[array_search('url', $this->CSVFields)], 
                $row[array_search('parameters', $this->CSVFields)], $row[array_search('expected result', $this->CSVFields)], 'failure in row '.json_encode($row));
        }

    }

    /**
     * CSVRowIsValid - validate a row from a CSV file
     * 
     * @param mixed $row 
     * @access protected
     * @return void
     */
    protected function CSVRowIsValid($row) {

        // check the method
        $method = $row[array_search('method', $this->CSVFields)];
        if (!in_array(strtolower($method), $this->requestMethods)) {
            return false;
        }

        // check the URL
        $url = $row[array_search('url', $this->CSVFields)];
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // check the parameters
        // - nothing for now
    
        // check the expected result
        // - nothing for now

        return true;
    }
}
