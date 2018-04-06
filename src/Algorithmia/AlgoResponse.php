<?php

namespace Algorithmia;

class AlgoResponse {
    private $httpResponse;
    public $json;
    public $result;
    public $metadata;

    /**
     * @param $in_httpResponse HttpResponse object
     * @param $in_result Object result object from algorithm call
     */
    public function __construct($in_httpResponse, $in_result) {
        $this->httpResponse = $in_httpResponse;
        $this->result = $in_result->result;
        $this->metadata = $in_result->metadata;
    }

    public function getHttpResponse(){
        return $this->httpResponse;
    }

    public function get(){
        return $this->result;
    }

    public function getMetadata(){
        return $this->metadata;
    }

}