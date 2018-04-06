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
     * @param mixed $in_input The input to send to the algorithm. Can be a string or an object.
     */
    public function doSynchronousCall(string $in_algo, $in_input) {
        $response = null;

        if (is_object($in_input) && get_class($in_input) == ByteArray::class) {
            $response = $this->doSynchronousBinaryCall($in_algo, $in_input->getData());
        } 
        else {
            $response = $this->doSynchronousJsonCall($in_algo, $in_input);
        }

        return $response;
    }

    private function getCallUrl($in_target) {
        return self::API_BASE_PATH . '/'. ltrim($in_target,'/');
    }

    /**
     * @param $in_url string of URL to call
     * @param $in_payload mixed payload to deliver to algorithm. Can be a string or an object.
     * @return Algorithmia\AlgoResponse
     */
    private function doSynchronousJsonCall(string $in_url, $in_payload = "") {
        $http_client = $this->getJsonHttpClient();

        $response = $http_client->post($in_url, ['json'=>$in_payload]);

        $str_result = $response->getBody()->getContents();
        $obj_result = json_decode($str_result);
        
        $algo_response = new AlgoResponse($response, $obj_result);

        return $algo_response;
    }

    /**
     * @param $in_url string of URL to call
     * @param $in_payload mixed payload to deliver to algorithm. Can be a string or an object.
     * @return Algorithmia\AlgoResponse
     */
    private function doSynchronousBinaryCall(string $in_url, $in_payload = "") {
        $http_client = $this->getBinaryHttpClient();

        $response = $http_client->post($in_url, ['body'=>$in_payload]);

        $str_result = $response->getBody()->getContents();

        $obj_result = json_decode($str_result);
        
        if($obj_result->metadata->content_type == "binary" && $obj_result->result)
        {
            $obj_result->result = base64_decode($obj_result->result);

            if ($obj_result->result === false) {
                throw new \Exception('base64_decode failed');
            }
        }

        $algo_response = new AlgoResponse($response, $obj_result);

        return $algo_response;
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
        return new \GuzzleHttp\Client($this->getGuzzleHttpOptions('application/json'));
    }

    private function createBinaryHttpClient()
    {
        return new \GuzzleHttp\Client($this->getGuzzleHttpOptions('application/octet-stream'));
    }

    private function getGuzzleHttpOptions($in_content_type){
        $options = [
            'base_uri' => self::API_BASE_PATH,
            'headers' => ['Content-Type' => $in_content_type]
        ];

        if(isset($this->key)){
            $options['headers']['Authorization'] = 'Simple '.$this->key;
        }

        return $options;
    }


}

