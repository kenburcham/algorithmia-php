<?php

namespace Algorithmia;

class DataDirectory extends DataObject {

    private $folders = [];
    private $files = [];
    
    /**
     * Call the Algorithmia API and populate ourselves.
     */
    public function sync($in_marker = null, $in_returnAcl = false)
    {
        $acl = ($in_returnAcl) ? "acl=true" : "acl=false"; 
        $path = (is_null($in_marker)) ? $this->path . '?' . $acl : $this->path . '?marker=' . $in_marker . '&' . $acl;

        //echo "calling: " . $path;

        $response = $this->client->doDataGet($this->connector, $path);

        $str_result = $response->getBody()->getContents();
        $obj_result = json_decode($str_result);

        //echo print_r($obj_result, true);

        if(property_exists($obj_result, 'error')){
            throw new AlgoException($obj_result->error->message);
        }

        if(property_exists($obj_result, 'files')){
            $this->files = array_merge($this->files, $this->asDataFiles($obj_result->files));
        }

        if(property_exists($obj_result, 'folders')){
            $this->folders = array_merge($this->folders, $this->asDataDirectories($obj_result->folders));
        }

        if(property_exists($obj_result, 'marker')){
            $this->sync($obj_result->marker); //recursively call until we have all of the files
        }

        if(property_exists($obj_result, 'acl')){
            $this->acl = $obj_result->acl;
        }

        $this->response = $response;
       
    }

    /** 
    * Create a directory 
    * @param 
    */
    public function create($in_acl = ACL::DEFAULT_PERMISSION)
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

    public function containsFolder($in_name){

        foreach($this->folders() as $folder){
            if($folder->name == $in_name )
                return true;
        }

        return false;
    }

    public function containsFile($in_name){

        foreach($this->files() as $file){
            if($file->name == $in_name )
                return true;
        }

        return false;
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
     * @param $in_name can be full path "data://.my/somefolder/myfile.txt" or "myfile.txt" located in this directory
     * @return DataFile file object
     */
    public function file($in_name){

        if(strpos($in_name, '://')){
            $file = new DataFile($in_name, $this->client); 
        } else {
            $file = new DataFile($this->getDataUrl().'/'.$in_name, $this->client);
        }

        return $file;
    }

    public function putFile($in_filepath){
        if(strpos($in_filepath,'/')===false){
            $filename = $in_filepath;
        }
        else
        {
            preg_match('((?P<parent>.*)\/(?P<name>.*))',$in_filepath, $name_parts);

            if(array_key_exists('name',$name_parts))
                $filename = $name_parts['name'];
            
                if(!isset($filename))
                throw new \Exception("filename is invalid.");
        }
        
        $file = new DataFile($this->getDataUrl().'/'.$filename, $this->client);
        return $file->putFile($in_filepath);
    }

    //note: "list" is a reserved word for PHP 5.6
    public function getList(){
        $this->sync();
        return array_merge($this->files, $this->folders);
    }

    public function getReadAcl(){
        $this->sync(null, true);
        return $this->acl->read[0];
    }

}
