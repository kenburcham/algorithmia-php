<?php

use PHPUnit\Framework\TestCase;

define("TEST_DIR_NAME", "test_php_".uniqid());

class BaseTest extends TestCase
{
    private $key;
    private $client;
    protected $testDir = __DIR__;

    public function getClient()
    {
        if (!$this->client) {
            $this->client = $this->createClient();
        }
        
        return $this->client;
    }

    private function createClient() {
        if (!$this->hasValidKey()) {
            $this->markTestSkipped("Test requires API key file to be set.");
            return false;
        }

        return Algorithmia::client($this->key);
    }
    
    public function hasValidKey()
    {
        if(!strlen($this->key)) {
            $this->key = $this->loadKey();
        }

        return (strlen($this->key));
    }

    public function loadKey()
    {
        $api_file = $this->testDir . DIRECTORY_SEPARATOR . 'apikey.txt';
        if(file_exists($api_file)) {
            return file_get_contents($api_file);
        }
    }
}