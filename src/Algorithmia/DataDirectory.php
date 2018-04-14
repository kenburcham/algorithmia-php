<?php

namespace Algorithmia;

class DataDirectory {

    private $client;
    
    private $dataUrl;
    private $connector;
    private $path;
    private $name; 

    private $folders;
    private $files;
    private $marker;
    private $acl;

    private $response;


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

        if(isset($this->client)){
            $this->sync();
        }
    }

    

    public function sync(){
        if(!isset($this->client)){
            throw new AlgoException("client must be set");
        }


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
