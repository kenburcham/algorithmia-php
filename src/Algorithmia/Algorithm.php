<?php

namespace Algorithmia;

/**
 * Represents an Algorithmia algorithm that can call on a user's behalf.
 */
class Algorithm {

    /**
     * Client we use to make API calls to the algorithm.
     * @var Client $client
     */
    private $client;

    /**
     * URL to our algorithm.
     * @var string $algoUrl
     */
    private $algoUrl;

    /**
     * Construct the Algorithmia/Algorithm
     * @param string $in_algo
     * @param Client $client
     */
    public function __construct( $in_algo, Client $in_client = null)
    {
        $this->client = $in_client;
        $this->algoUrl = $in_algo;
    }

    /**
     * Execute an API call for this Algorithm
     * @param mixed $in_input The input to send to the algorithm.
     * @return AlgoResponse the AlgoResponse object for the result
     */
    public function pipe($in_input) 
    {
        return $this->client->doAlgoPipe($this->algoUrl, $in_input);
    }

    /**
     * Execute an API call for this Algorithm asynchronously. 
     * @param mixed $in_input The input to send to the algorithm.
     * @return \GuzzleHttp\Promise\PromiseInterface Promise which return AlgoResponse when resolved
     */
    public function pipeAsync($in_input)
    {
        return $this->client->doAlgoPipe($this->algoUrl, $in_input, true);
    }

    /**
     * Set options on the client such as timeout
     * @param array $in_options An array of options: ['timeout' => 120]
     * @return self $this
     */
    public function setOptions(array $in_options = array()) 
    {
        $this->client->setOptions($in_options);
        return $this;
    }



}