<?php

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Ring\Client\StreamHandler;
use GuzzleHttp\Middleware;

namespace Algorithmia;

class Client {
    const LIBVER = "1.0";
    const USER_AGENT_SUFFIX = "algorithmia-php-client";
    const API_BASE_PATH = "https://api.algorithmia.com/v1/algo/";

    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_TEXT = "application/text";
    const CONTENT_TYPE_OCTET_STREAM = "application/octet-stream";

    /**
     * Algorithmia API key
     * @var string $key
     */
    private $key;

    /**
     * Guzzle http client configured with json headers
     * @var GuzzleHttp\ClientInterface $http
     */
    private $json_http;
    
    /**
     * Guzzle http client configured with binary headers
     * @var GuzzleHttp\ClientInterface $http
     */
    private $bin_http;
    
    /**
     * Options that can be configured for the client
     * @var array
     */
    private $options = array(
        'timeout' => 90,
        'server' => self::API_BASE_PATH,
        'agent' => self::USER_AGENT_SUFFIX,
        'version' => self::LIBVER
    );

    /**
     * Construct the Algorithmia client
     * @param string $in_key 
     * @param string $in_baseurl URL for the server: "https://api.algorithmia.com/v1/algo/"
     */
    public function __construct($in_key, $in_baseurl=null) {
        $this->key = preg_replace('/\n/','',$in_key);

        //make sure there is a trailing slash
        if(!is_null($in_baseurl)) {
            $this->options['server'] = rtrim($in_baseurl,"/")."/";
        }
    }

    /**
     * Get version of the library.
     * @return string
     */
    public function getVersion()
    {
        return self::LIBVER;
    }

    /**
     * Set options for the Algo Client
     * @param array Array of parameters:  ['timeout' => 120, 'server' => 'https://api.algorithmia.com/v2/algo/']
     * @return Algorithmia\Client
     */
    public function setOptions(array $in_options = array()) {
        $this->options = array_merge($this->options, $in_options);

        //setting the options needs to drop the cached guzzle clients so they will be recreated
        $this->json_http = null;
        $this->bin_http = null;

        return $this;
    }

    public function getOptions() {
        return $this->options;
    }

    /**
     * Get an Algorithmia\Algorithm that represents the algorithm to call.
     * @param string $in_algo The algorithm to call.
     * @return Algorithmia\Algorithm
     */
    public function algo(string $in_algo) {
        return new Algorithm($in_algo, $this);
    }

    /**
     * Get an Algorithmia\DataDirectory that represents a directory.
     * @param string $in_dataurl The data directory url, e.g.: data://.my/folder
     * @return Algorithmia\DataDirectory
     */
    public function dir(string $in_dataurl) {
        return new DataDirectory($in_dataurl);
    }

    /**
     * Do the synchronous call and return the result.
     * @param string $in_algo The algorithm to call.
     * @param mixed $in_input The input to send to the algorithm. Can be a string or an object.
     * @return Algorithmia\AlgoResponse the AlgoResponse object for the result
     */
    public function doSynchronousCall(string $in_algo, $in_input) {
        $response = null;

        //call either json or binary depending on the input
        if (is_object($in_input) && get_class($in_input) == ByteArray::class) {
            $response = $this->doSynchronousBinaryCall($in_algo, $in_input->getData());
        } 
        else {
            $response = $this->doSynchronousJsonCall($in_algo, $in_input);
        }

        $str_result = $response->getBody()->getContents();
        $obj_result = json_decode($str_result);

        if(property_exists($obj_result, 'error'))
        {
            throw new AlgoException($obj_result->error->message);
        }

        //var_dump($obj_result);

        //convert results if they are binary
        if($obj_result->metadata->content_type == "binary" && $obj_result->result)
        {
            $obj_result->result = base64_decode($obj_result->result);

            if ($obj_result->result === false) {
                throw new \Exception('base64_decode failed to decode the result');
            }
        }

        $algo_response = new AlgoResponse($response, $obj_result);

        return $algo_response;
    }

    /**
     * @param $in_url string of URL to call
     * @param $in_payload mixed payload to deliver to algorithm. Can be a string or an object.
     * @return httpresponse Object
     */
    private function doSynchronousJsonCall(string $in_url, $in_payload = "") {
        $http_client = $this->getJsonHttpClient();

        $response = $http_client->post($in_url, ['json' => $in_payload, 'timeout' => $this->options['timeout']]);

        return $response;
    }

    /**
     * @param $in_url string of URL to call
     * @param $in_payload mixed payload to deliver to algorithm. Can be a string or an object.
     * @return httpresponse object
     */
    private function doSynchronousBinaryCall(string $in_url, $in_payload = "") {
        $http_client = $this->getBinaryHttpClient();

        $response = $http_client->post($in_url, ['body' => $in_payload, 'timeout' => $this->options['timeout']]);

        return $response;
    }

    public function getBinaryHttpClient()
    {
        if(null === $this->bin_http) {
            $this->bin_http = $this->createBinaryHttpClient();
        }

        return $this->bin_http;
    }

    public function getJsonHttpClient()
    {
        if(null === $this->json_http) {
            $this->json_http = $this->createJsonHttpClient();
        }

        return $this->json_http;
    }

    private function createJsonHttpClient()
    {
        return new \GuzzleHttp\Client($this->getGuzzleHttpOptions(self::CONTENT_TYPE_JSON));
    }

    private function createBinaryHttpClient()
    {
        return new \GuzzleHttp\Client($this->getGuzzleHttpOptions(self::CONTENT_TYPE_OCTET_STREAM));
    }

    private function getGuzzleHttpOptions($in_content_type){
        $header_options = [
            'base_uri' => $this->options['server'],
            'headers' => ['Content-Type' => $in_content_type]
        ];

        if(isset($this->key)){
            $header_options['headers']['Authorization'] = 'Simple '.$this->key;
        }

        return $header_options;
    }


}

