<?php

namespace Algorithmia;

class Client {
    const LIBVER = "1.0";

    //defaults
    const API_BASE_PATH = "https://api.algorithmia.com";
    const API_VERSION = "/v1/";
    const ALGO_SUFFIX = "algo/";

    /**
     * Algorithmia API key
     * @var string $key
     */
    private $key;

    /**
     * http client that manages our http calls
     * @var HttpClient $http
     */
    private $http_client;


    private $api_address = self::API_BASE_PATH;

    /**
     * Construct the Algorithmia client
     * @param string $in_key 
     * @param string $in_baseurl URL for the server: "https://api.algorithmia.com"
     */
    public function __construct($in_key=null, $in_baseurl=null) {

        $api_key = (!is_null($in_key)) ? $in_key : getenv('ALGORITHMIA_API_KEY');
        $baseurl = (!is_null($in_baseurl)) ? $in_baseurl : getenv('ALGORITHMIA_API');

        if($api_key)
            $this->key = preg_replace('/\n/','',$api_key);

        if($baseurl)
            $this->api_address = $this->getDomainFromURL($baseurl);

        $this->http_client = new HttpClient(['server' => $this->api_address, 'key' => $this->key]);
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
     * @param array Array of parameters:  ['timeout' => 120, 'server' => 'https://api.algorithmia.com']
     * @return HttpClient
     */
    public function setOptions(array $in_options = array()) {
        if(array_key_exists('server',$in_options))
        {
            $in_options['server'] = $this->getDomainFromURL($in_options['server']);
            $this->api_address = $in_options['server'];
        }
        return $this->http_client->setOptions($in_options);
    }

    public function getOptions() {
        return $this->http_client->getOptions();
    }

    /**
     * Get an Algorithmia\Algorithm that represents the algorithm to call.
     * @param string $in_algo The algorithm to call.
     * @return Algorithm
     */
    public function algo($in_algo) {
        return new Algorithm($in_algo, $this);
    }

    /**
     * Get an Algorithmia\DataDirectory that represents a directory.
     * @param string $in_dataurl The data directory url, e.g.: data://.my/folder
     * @return DataDirectory
     */
    public function dir($in_dataurl) {
        return new DataDirectory($in_dataurl, $this);
    }

    /**
     * Get an Algorithmia\DataFile that represents a file.
     * @param string $in_dataurl The full path to the file, e.g.: data://.my/folder/file.txt
     * @return DataFile
     */
    public function file($in_datafile) {
        return new DataFile($in_datafile, $this);
    }

    public function doDataPut($in_connector, $in_path, $in_input){
        $data_url = $this->getDataUrl($in_connector, $in_path);
        $response = $this->http_client->put($data_url, $in_input, HttpClient::CONTENT_TYPE_OCTET_STREAM);

        return $response;
    }

    public function doFileGet($in_connector, $in_path){
        $data_url = $this->getDataUrl($in_connector, $in_path);
        $response = $this->http_client->get($data_url, HttpClient::CONTENT_TYPE_OCTET_STREAM);

        return $response;  
    }

    public function doDataGet($in_connector, $in_path){

        $data_url = $this->getDataUrl($in_connector, $in_path);
        $response = $this->http_client->get($data_url, HttpClient::CONTENT_TYPE_JSON);

        return $response;

    }

    public function doDataPost($in_connector, $in_path, $in_input){
        $data_url = $this->getDataUrl($in_connector, $in_path);
        $content_type = HttpClient::CONTENT_TYPE_JSON;

        $response = $this->http_client->post($data_url, $in_input, HttpClient::CONTENT_TYPE_JSON);

        return $response;

    }

    public function doDataDelete($in_connector, $in_path){
        $data_url = $this->getDataUrl($in_connector, $in_path);
        $content_type = HttpClient::CONTENT_TYPE_JSON;
        
        $response = $this->http_client->delete($data_url, HttpClient::CONTENT_TYPE_JSON);

        return $response;
    }

    /**
     * Do the synchronous POST call and return the result.
     * @param string $in_algo The algorithm to call.
     * @param mixed $in_input The input to send to the algorithm. Can be a string or an object.
     * @return AlgoResponse|\GuzzleHttp\Promise\PromiseInterface the AlgoResponse object for the result
     */
    public function doAlgoPipe($in_algo, $in_input, $in_async = false) {

        $algo_url = $this->getAlgoUrl($in_algo);

        $content_type = HttpClient::CONTENT_TYPE_JSON;
        $input = $in_input;

        //check if it is binary input
        if (is_object($in_input) && get_class($in_input) == ByteArray::class) {
            $input = $in_input->getData();
            $content_type = HttpClient::CONTENT_TYPE_OCTET_STREAM;
        }

        $response = $this->http_client->post($algo_url, $input, $content_type, $in_async);

        //if they've requested a direct return with no waiting, return early with the request id
        if($this->getOptions()['output'] == 'void' || $in_async)
        {
            //if this is an async request then once the promise resolves, build and return our algoresponse
            return $response
                ->then(function($server_response) {
                    try{
                        $algoresponse = $this->buildAlgoResponse($server_response);
                    }catch(\Exception $e) {
                        //these are hard to see otherwise... lets help folks out.
                        echo "Internal error in promise then: ".$e->getMessage();
                        echo $e->getTraceAsString();
                        throw new \Algorithmia\AlgoException($e->getMessage());
                    }

                    return $algoresponse;
                },
                function($exception){
                    throw new \Algorithmia\AlgoException($exception->getMessage());
                });
        }

        return $this->buildAlgoResponse($response);
    }

    //builds the algoresponse from the response object
    public function buildAlgoResponse(\Psr\Http\Message\ResponseInterface $response) {
        $str_result = $response->getBody()->getContents();

        if($this->getOptions()['output'] == 'raw')
        {
            return $str_result; //if they've requested raw output, return early.
        }

        $obj_result = json_decode($str_result);

        if(!$obj_result)
            return $str_result;

        if(property_exists($obj_result, 'error'))
        {
            throw new AlgoException($obj_result->error->message);
        }

        if(!property_exists($obj_result, 'result')) //this will be the case for output=void
        {
            return $obj_result;
        }

        //convert results if they are binary
        if($obj_result->metadata->content_type == "binary" && $obj_result->result)
        {
            $obj_result->result = base64_decode($obj_result->result);

            if ($obj_result->result === false) {
                throw new \Exception('base64_decode failed to decode the result');
            }
        }
        
        return new AlgoResponse($response, $obj_result);
    }

    /**
     * Builds the data url from the server + "/v1/" + connector e.g.: "https://api.algorithmia.com/v1/data/"
     * @return string data api url
     */
    public function getDataUrl($in_connector, $in_path){
        return $this->api_address . self::API_VERSION . 'connector/' . $in_connector . "/".$in_path;
    }

    public function getAlgoUrl($in_algo){
        return $this->api_address . self::API_VERSION . self::ALGO_SUFFIX . $in_algo;
    }

    public function getDomainFromURL($in_url){
        preg_match('/^(?P<domain>https?:\/\/[^\/]*)/',$in_url,$url_parts);
        return $url_parts['domain'];
    }
}

