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
     * @var Algorithmia\HttpClient $http
     */
    private $http_client;


    private $api_address = self::API_BASE_PATH;

    /**
     * Construct the Algorithmia client
     * @param string $in_key 
     * @param string $in_baseurl URL for the server: "https://api.algorithmia.com"
     */
    public function __construct($in_key, $in_baseurl=null) {
        $this->key = preg_replace('/\n/','',$in_key);

        if(!is_null($in_baseurl))
            $this->api_address = $this->getDomainFromURL($in_baseurl);

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
     * @return Algorithmia\Client
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
        return new DataDirectory($in_dataurl, $this);
    }

    /**
     * Get an Algorithmia\DataFile that represents a file.
     * @param string $in_dataurl The full path to the file, e.g.: data://.my/folder/file.txt
     * @return Algorithmia\DataFile
     */
    public function file(string $in_datafile) {
        return new DataFile($in_datafile, $this);
    }

    public function doDataPut(string $in_connector, string $in_path, $in_input){
        $data_url = $this->getDataUrl($in_connector, $in_path);
        $response = $this->http_client->put($data_url, $in_input, HttpClient::CONTENT_TYPE_OCTET_STREAM);

        return $response;
    }

    public function doFileGet(string $in_connector, string $in_path){
        $data_url = $this->getDataUrl($in_connector, $in_path);
        $response = $this->http_client->get($data_url, HttpClient::CONTENT_TYPE_OCTET_STREAM);

        return $response;  
    }

    public function doDataGet(string $in_connector, string $in_path){

        $data_url = $this->getDataUrl($in_connector, $in_path);
        $response = $this->http_client->get($data_url, HttpClient::CONTENT_TYPE_JSON);

        return $response;

    }

    public function doDataPost(string $in_connector, string $in_path, $in_input){
        $data_url = $this->getDataUrl($in_connector, $in_path);
        $content_type = HttpClient::CONTENT_TYPE_JSON;

        $response = $this->http_client->post($data_url, $in_input, HttpClient::CONTENT_TYPE_JSON);

        return $response;

    }

    public function doDataDelete(string $in_connector, string $in_path){
        $data_url = $this->getDataUrl($in_connector, $in_path);
        $content_type = HttpClient::CONTENT_TYPE_JSON;
        
        $response = $this->http_client->delete($data_url, HttpClient::CONTENT_TYPE_JSON);

        return $response;
    }

    /**
     * Do the synchronous POST call and return the result.
     * @param string $in_algo The algorithm to call.
     * @param mixed $in_input The input to send to the algorithm. Can be a string or an object.
     * @return Algorithmia\AlgoResponse the AlgoResponse object for the result
     */
    public function doAlgoPipe(string $in_algo, $in_input) {

        $algo_url = $this->getAlgoUrl($in_algo);

        $content_type = HttpClient::CONTENT_TYPE_JSON;
        $input = $in_input;

        //check if it is binary input
        if (is_object($in_input) && get_class($in_input) == ByteArray::class) {
            $input = $in_input->getData();
            $content_type = HttpClient::CONTENT_TYPE_OCTET_STREAM;
        }

        $response = $this->http_client->post($algo_url, $input, $content_type);

        //if they've requested a direct return with no waiting, return early.
        if($this->getOptions()['output'] == 'void')
        {
            return $response;
        }

        $str_result = $response->getBody()->getContents();

        if($this->getOptions()['output'] == 'raw')
        {
            return $str_result; //if they've requested raw output, return early.
        }
        
        $obj_result = json_decode($str_result);

        if(property_exists($obj_result, 'error'))
        {
            throw new AlgoException($obj_result->error->message);
        }

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
     * Builds the data url from the server + "/v1/" + connector e.g.: "https://api.algorithmia.com/v1/data/"
     * @return string data api url
     */
    public function getDataUrl(string $in_connector, string $in_path){
        return $this->api_address . self::API_VERSION . 'connector/' . $in_connector . "/".$in_path;
    }

    public function getAlgoUrl($in_algo){
        return $this->api_address . self::API_VERSION . self::ALGO_SUFFIX . $in_algo;
    }

    public function getDomainFromURL(string $in_url){
        preg_match('/^(?P<domain>https?:\/\/[^\/]*)/',$in_url,$url_parts);
        return $url_parts['domain'];
    }
}

