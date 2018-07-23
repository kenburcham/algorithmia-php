<?php

namespace Algorithmia;

class AlgoResponse {
    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    private $httpResponse;
    public $result;
    public $metadata;

    /**
     * @param $in_httpResponse \Psr\Http\Message\ResponseInterface object
     * @param $in_result Object result object from algorithm call
     */
    public function __construct($in_httpResponse, $in_result) {
        $this->httpResponse = $in_httpResponse;
        $this->result = $in_result->result;
        $this->metadata = $in_result->metadata;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
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