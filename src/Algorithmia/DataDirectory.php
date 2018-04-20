<?php

namespace Algorithmia;

class DataDirectory {

    private $client;
    
    private $dataUrl;
    private $connector;
    private $path;
    private $name; 
    private $parent;

    private $folders;
    private $files;
    private $marker;
    private $acl;

    private $response;


    /**
     * Constructs a DataDirectory object ready for fetching or creating
     * @param string $in_dataurl The URL for the datadirectory to represent
     * @param Algorithmia\Client $client The client object to use if you want to actually connect.
     * @return Algorithmia\DataDirectory 
     */
    public function __construct(string $in_dataurl, Client $in_client = null){
        $this->client = $in_client;
        $this->dataUrl = rtrim($in_dataurl,"/");

        preg_match('/(?P<connection>\w+):\/\/(?P<path>.*)/', $this->dataUrl, $url_parts);

        $this->connector = $url_parts['connection'];
        $this->path = $url_parts['path'];

        preg_match('((?P<parent>.*)\/(?P<name>.*))',$this->path, $name_parts);
        if(array_key_exists('name',$name_parts))
            $this->name = $name_parts['name'];
        
        if(array_key_exists('parent',$name_parts))
            $this->parent = $name_parts['parent'];

        if(!DataConnectors::isValidConnector($this->connector)){
            throw new AlgoException("connection type is invalid: "+ $this->connector);
        }
    }

    
    /**
     * Call the Algorithmia API and populate ourselves.
     */
    public function sync(){
        if(is_null($this->client)){
            throw new AlgoException("client must be set");
        }

        $response = $this->client->doDataGet($this->connector, $this->path);
        if(property_exists($response, 'files')){
            $this->files = $response->files;
        }
        if(property_exists($response, 'folders')){
            $this->folders = $response->folders;
        }

        $this->response = $response;
    }

    /** 
    * Create a directory 
    * @param 
    */
    public function create($in_acl = ACL::DEFAULT)
    {
        if(is_null($this->client)){
            throw new AlgoException("client must be set");
        }

        $input = ["name" => $this->name, "acl" => ACL::getACLJson($in_acl)];
        $this->response = $this->client->doDataPost($this->connector, $this->parent, $input);

        return $this;
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

    public function getParent(){
        return $this->parent;
    }

    public function folders(){
        $this->sync();
        return $this->folders;
    }

    public function files(){
        $this->sync();
        return $this->files;
    }

    public function marker(){
        return $this->marker();
    }

    public function acl(){
        return $this->acl;
    }

    public function getResponse(){
        return $this->response;
    }

}
