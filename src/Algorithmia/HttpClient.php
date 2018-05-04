<?php

namespace Algorithmia;

class HttpClient {
    const USER_AGENT_SUFFIX = "algorithmia-php-client";

    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_TEXT = "application/text";
    const CONTENT_TYPE_OCTET_STREAM = "application/octet-stream";

    const DEBUG = false;

     /**
     * Guzzle http client configured with json headers
     * @var GuzzleHttp\ClientInterface $json_http
     */
    private $json_http;
    
    /**
     * Guzzle http client configured with binary headers
     * @var GuzzleHttp\ClientInterface $bin_http
     */
    private $bin_http;


    /**
     * Options that can be configured for the client
     * @var array
     */
    private $options = array(
        'timeout' => 300, //default timeout is 300s = 5 minutes
        'server' => null,
        'agent' => self::USER_AGENT_SUFFIX,
        'key' => null,
        'debug' => self::DEBUG
    );

    public function __construct(array $in_options = array()){
        $this->setOptions($in_options);
    }

    public function getOptions(){
        return $this->options;
    }

    /**
     * Set options for the Algo Client
     * @param array Array of parameters:  ['timeout' => 120, 'server' => 'https://api.algorithmia.com']
     * @return Algorithmia\Client
     */
    public function setOptions(array $in_options = array()) {
        $this->options = array_merge($this->options, $in_options);

        //setting the options needs to drop the cached clients so they will be recreated
        $this->json_http = null;
        $this->bin_http = null;

        return $this;
    }

    public function get(string $in_url, string $in_content_type){
        $client = $this->getClientForType($in_content_type);
        return $client->get($in_url, ['timeout' => $this->options['timeout'], 'debug' => $this->options['debug']]);        
    }

    /**
     * @param $in_url string of URL to call with PUT
     * @param $in_input mixed payload to deliver to api endpoint. Can be a string or an object.
     * @return httpresponse Object
     */
    public function put(string $in_url, $in_input, string $in_content_type){

        $client = $this->getClientForType($in_content_type);
        $body_name = $this->getBodyNameForType($in_content_type);
        
        return $client->put($in_url, [$body_name => $in_input, 'timeout' => $this->options['timeout'], 'debug' => $this->options['debug']]);
    }

    /**
     * @param $in_url string of URL to call with POST
     * @param $in_input mixed payload to deliver to api endpoint. Can be a string or an object.
     * @return httpresponse Object
     */
    public function post(string $in_url, $in_input, string $in_content_type){

        $client = $this->getClientForType($in_content_type);
        $body_name = $this->getBodyNameForType($in_content_type);
        
        return $client->post($in_url, [$body_name => $in_input, 'timeout' => $this->options['timeout'], 'debug' => $this->options['debug']]);
    }

    public function delete(string $in_url, string $in_content_type) {
        $client = $this->getClientForType($in_content_type);
        return $client->delete($in_url);
    }

    public function getClientForType(string $in_content_type) {
        if($in_content_type == self::CONTENT_TYPE_JSON)
        {
            $client = $this->getJsonHttpClient();
        }
        else if($in_content_type == self::CONTENT_TYPE_OCTET_STREAM) {
            $client = $this->getBinaryHttpClient();
        }
        return $client;
    }

    public function getBodyNameForType(string $in_content_type) {
        if($in_content_type == self::CONTENT_TYPE_JSON)
        {
            $body_name = 'json';
        }
        else if($in_content_type == self::CONTENT_TYPE_OCTET_STREAM) {
            $body_name = 'body';
        }
        return $body_name;
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

        if(!is_null($this->options['key'])){
            $header_options['headers']['Authorization'] = 'Simple '.$this->options['key'];
        }

        return $header_options;
    }


}