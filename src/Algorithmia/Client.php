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

    const DEFAULT_CONTENT_TYPE = self::CONTENT_TYPE_JSON;

    /**
     * Algorithmia API key
     * @var string $key
     */
    private $key;

    /**
     * Guzzle http client
     * @var GuzzleHttp\ClientInterface $http
     */
    private $http;
    
    /**
     * Construct the Algorithmia client
     * @param string $in_key 
     */
    public function __construct($in_key) {
        $this->key = preg_replace('/\n/','',$in_key);
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
     * Get an Algorithmia\Algorithm that represents the algorithm to call.
     * @param string $in_algo The algorithm to call.
     * @return Algorithmia\Algorithm
     */
    public function algo(string $in_algo) {
        return new Algorithm($in_algo, $this);
    }

    /**
     * Do the synchronous call and return the result.
     * @param string $in_algo The algorithm to call.
     * @param mixed $in_input The input to send to the algorithm.
     */
    public function doSynchronousCall(string $in_algo, $in_input) {
        //$url_target = $this->getCallUrl($in_algo);
        return $this->doSynchronousPostCall($in_algo, $in_input);
    }

    private function getCallUrl($in_target) {
        return self::API_BASE_PATH . '/'. ltrim($in_target,'/');
    }

    /**
     * @param $in_url string of URL to call
     * @param $in_payload mixed payload to deliver to algorithm. Can be a json string or an object.
     * @return Algorithmia\AlgoResponse
     */
    private function doSynchronousPostCall(string $in_url, $in_payload = "") {
        $http_client = $this->getHttpClient();
        $response = $http_client->post($in_url, ['json'=>$in_payload]);

        $str_result = $response->getBody()->getContents();
        $obj_result = json_decode($str_result);
        
        $algo_response = new AlgoResponse($response, $obj_result);

        return $algo_response;
    }

    public function getHttpClient()
    {
        if(null === $this->http) {
            $this->http = $this->createDefaultHttpClient();
        }

        return $this->http;
    }

    private function createDefaultHttpClient()
    {
        return new \GuzzleHttp\Client($this->getDefaultGuzzleHttpOptions());
    }

    private function getDefaultGuzzleHttpOptions(){
        $options = [
            'base_uri' => self::API_BASE_PATH,
            'headers' => ['Content-Type' => 'application/json']
        ];

        if(isset($this->key)){
            $options['headers']['Authorization'] = 'Simple '.$this->key;
        }

        return $options;
    }


}

