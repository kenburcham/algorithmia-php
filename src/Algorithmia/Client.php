<?php

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Ring\Client\StreamHandler;

namespace Algorithmia;

class Client {
    const LIBVER = "1.0";
    const USER_AGENT_SUFFIX = "algorithmia-php-client";
    const API_BASE_PATH = "https://algorithmia.com/api";

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
        $this->key = $in_key;
    }

    /**
     * Get version of the library.
     * @return string
     */
    public function getVersion()
    {
        return self::LIBVER;
    }

}