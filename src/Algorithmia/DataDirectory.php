<?php

namespace Algorithmia;

class DataDirectory extends DataObject {

    private $folders;
    private $files;
    private $marker;
    
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

    public function folders(){
        $this->sync();
        return $this->folders;
    }

    public function files(){
        $this->sync();
        return $this->files;
    }

    /**
     * Gets a reference to a directory's child DataFile
     */
    public function file($in_name){
        return new DataFile($in_name, $this->client);
    }

    public function putFile(DataFile $in_file){
        
    }

    public function list(){
        $this->sync();
        return array_merge($this->files, $this->folders);
    }

    public function marker(){
        return $this->marker();
    }

}
