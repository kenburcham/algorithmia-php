<?php

namespace Algorithmia;

class AlgoResponse {
    private $httpResponse;
    public $json;
    public $result;
    public $metadata;

    public function __construct($in_httpResponse, $in_result) {
        $this->httpResponse = $in_httpResponse;
        
        $json_result = json_decode($in_result);
        
        $this->result = $json_result->result;
        $this->metadata = $json_result->metadata;
    }

    public function getHttpResponse(){
        return $this->httpResponse;
    }

    public function getResult(){
        return $this->result;
    }

    public function getMetadata(){
        return $this->metadata;
    }

    public function setHttpResponse($in_httpResponse){
        $this->httpResponse = $in_httpResponse;
    }

    public function setResult($in_result){
        $this->result = $in_result;
    }

    public function setMetadata($in_metadata){
        $this->metadata = $in_metadata;
    }


}