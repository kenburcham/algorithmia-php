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
    public function sync()
    {
        $response = $this->client->doDataGet($this->connector, $this->path);

        $str_result = $response->getBody()->getContents();
        $obj_result = json_decode($str_result);

        if(property_exists($obj_result, 'error'))
        {
            throw new AlgoException($obj_result->error->message);
        }

        if(property_exists($obj_result, 'files')){
            $this->files = $obj_result->files;
        }
        if(property_exists($obj_result, 'folders')){
            $this->folders = $obj_result->folders;
        }

        $this->response = $response;
    }

    /** 
    * Create a directory 
    * @param 
    */
    public function create($in_acl = ACL::DEFAULT)
    {
        $input = ["name" => $this->name, "acl" => ACL::getACLJson($in_acl)];

        $this->response = $this->client->doDataPost($this->connector, $this->parent, $input);

        return $this;
    }

    public function delete($in_force = false)
    {
        $path_force = ($in_force) ? $this->path . "?force=true" : $this->path;

        $this->response = $this->client->doDataDelete($this->connector, $path_force);

        return $this;
    }

    public function containsFolder(string $in_name){

        $containsFolder = false;

        foreach($this->folders() as $folder){
            if($folder->name == $in_name )
                $containsFolder = true;
        }

        return $containsFolder;
    }

    public function exists(){
        $response = $this->client->doDataGet($this->connector, $this->path);
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
