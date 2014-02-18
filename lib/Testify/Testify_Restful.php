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
 * // add CSV file with 4 columns:
 * // | method (get/post/put/..) | url | parameters (JSON) | expected result |
 * $tfr->addCSVFile('some_file.csv');
 *
 * // assert restful service
 * $tfr->assertRESTFul($method, $url, $params, $expectedResultOrMethod, $message = '')
 * // example #1:
 * $tfr->assertRESTFul($method = 'POST', $url = 'http://example.com/users', $params = array('username'=>'sgchris'), 
 *      $expectedResult = '{"result":"ok"}');
 * // example #2:
 * $tfr->assertRESTFul($method = 'POST', $url = 'http://example.com/users', $params = array('username'=>'sgchris'), function($result) {
 *      // manipulate the response and return boolean value
 *      return !empty($result) && strlen($result) == 10;
 * });
 *
 * // execute the test
 * $tfr();
 *
 * @author Gregory Chris <sgchris@gmail.com>
 * @url https://github.com/sgchris
 */
namespace Testify;

class Testify_Restful extends Testify {

    private $requestMethods = ['get', 'post', 'put', 'delete', 'head', 'options'];
    protected $CSVFields = ['method', 'url', 'parameters', 'expected result'];

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
        if (!$this->CURLInstalled() || false == ($ch = @curl_init())) {
            return $this->recordTest(false, 'Error initializing CURL PHP module');
        }

        // prepare parameters for the request
        $parametersStr = is_array($parameters) && !empty($parameters) 
            ? http_build_query($parameters) 
            : (is_string($parameters) && !empty($parameters) ? $parameters : null);

        // set the request method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // set the URL
        curl_setopt($ch, CURLOPT_URL, $url);

        // set the request parameters
        if (!is_null($parametersStr)) {
            // content-length needed for PUT requests
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($parametersStr))); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parametersStr);
        }

        // set the return as string flag
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // execute the call
        $response = curl_exec($ch);

        // compare the result with the expected result
        if (is_string($expectedResultOrMethod)) {
            return $this->recordTest($response == $expectedResultOrMethod, $message);
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

            // validate the row
            if (!$this->CSVRowIsValid($row)) 
                continue;

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
