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

    public $response;


    /**
     * Constructs a DataObject ready for fetching or creating
     * @param string $in_dataurl The URL for the DataObject to represent
     * @param Client $client The client object to use if you want to actually connect.
     */
    public function __construct($in_dataurl, Client $in_client = null){
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
            throw new AlgoException("connection type is invalid: " . $this->connector);
        }
    }

    /**
     * convert an incoming array of stdClass files to bona fide DataFile objects
     */
    public function asDataFiles(array $in_files) {

        $data_files = array();

        foreach ($in_files as $file) {
            if(property_exists($file, 'filename')) {
                
                $data_file = new DataFile($this->getDataUrl().'/'.$file->filename, $this->client);

                foreach (get_object_vars($file) as $key => $value) {
                    $data_file->$key = $value;
                }
                array_push($data_files, $data_file);
            }
        }

        return $data_files;
    }

    /**
     * convert an incoming array of stdClass directories to bona fide DataDirectory objects
     */
    public function asDataDirectories(array $in_dirs) {

        $data_dirs = array();

        foreach ($in_dirs as $dir){
            if(property_exists($dir, 'name')) {

                $data_dir = new DataDirectory($this->getDataUrl().'/'.$dir->name, $this->client);

                foreach (get_object_vars($dir) as $key => $value) {
                    $data_dir->$key = $value;
                }
                array_push($data_dirs, $data_dir);
            }
        }

        return $data_dirs;
    }

    /**
     * Returns a DataDirectory object representing the parent
     */
    public function parent(){
        return new DataDirectory($this->connector . "://" . $this->parent, $this->client);
    }

    public function exists(){
        try{
            $response = $this->client->doDataGet($this->connector, $this->path);
            $this->response = $response;
            return ($response->getStatusCode() == 200);
        }
        catch(\Exception $e) //if 404 then the client throws an exception instead of just returning
        {
            return false;
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

    public function getParent(){
        return $this->parent;
    }

    public function getResponse(){
        return $this->response;
    }
}