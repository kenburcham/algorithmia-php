<?php

namespace Algorithmia;

class DataDirectory {

    private $client;
    
    private $connector;
    private $path;
    private $dataUrl;
    private $name; 

    private $folders;
    private $files;
    private $marker;
    private $acl;

    private $response;

    const DATA_API_VERSION = "/v1/";

    public function __construct(string $in_dataurl, Client $in_client = null){
        $this->client = $in_client;
        $this->dataUrl = rtrim($in_dataurl,"/");

        preg_match('/(?P<connection>\w+):\/\/(?P<path>.*)/', $this->dataUrl, $url_parts);

        $this->connector = $url_parts['connection'];
        $this->path = $url_parts['path'];

        preg_match('/(?P<name>[^\/]*)$/',$this->path, $name_parts);
        $this->name = $name_parts['name'];

        if(!DataConnectors::isValidConnector($this->connector)){
            throw new AlgoException("connection type is invalid: "+ $this->connector);
        }
    }

    /**
     * Builds the data url from the server + "/v1/" + connector e.g.: "https://api.algorithmia.com/v1/data"
     * @return string data api url
     */
    public function getDataAPIUrl(){
        if(!isset($this->client)){
            throw new AlgoException("client must be set");
        }

        preg_match('/^(?P<domain>https?:\/\/[^\/]*)/',$this->client->getOptions()['server'],$url_parts);
        return $url_parts['domain'] . self::DATA_API_VERSION . $this->connector . "/";
    }

    public function getConnector(){
        return $this->connector;
    }

    public function getPath(){
        return $this->path;
    }

    public function getDataUrl(){
        return $this->dataUrl;
    }

    public function getName(){
        return $this->name;
    }

    public function folders(){
        return $this->folders;
    }

    public function files(){

        return $this->files;
    }

    public function marker(){
        return $this->marker();
    }

    public function acl(){
        return $this->acl;
    }

}
