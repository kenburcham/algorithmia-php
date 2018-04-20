<?php

namespace Algorithmia;

class DataObject {

    protected $client;
    
    protected $dataUrl;
    protected $connector;
    protected $path;
    protected $name; 
    protected $parent;

    protected $acl;

    protected $response;


    /**
     * Constructs a DataObject ready for fetching or creating
     * @param string $in_dataurl The URL for the DataObject to represent
     * @param Algorithmia\Client $client The client object to use if you want to actually connect.
     * @return Algorithmia\DataObject 
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
     * Returns a DataDirectory object representing the parent
     */
    public function parent(){
        return new DataDirectory($connector . "://" . $this->parent);
    }

    public function exists(){
        try{
            $response = $this->client->doDataGet($this->connector, $this->path);
        }
        catch(\Exception $e) //if 404 then the client throws an exception instead of just returning
        {
            $response = $e->getResponse(); 
        }
        
        $this->response = $response;

        return ($response->getStatusCode() == 200);
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

    public function acl(){
        return $this->acl;
    }

    public function getResponse(){
        return $this->response;
    }
}