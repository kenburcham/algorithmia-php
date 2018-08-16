<?php

namespace Algorithmia;

class DataFile extends DataObject {

    /**
     * When you use getFile, the contents of the file is put into $this->result
     */
    public $result; 
    public $last_modified;
    public $size;
    public $content_type;

    /**
     * Download the file into a filesystem file ("filesink") and return a PHP file handle to it
     * @in_targetfilepath string Provide the path/to/file where you'd like to save the file into.
     *  if you don't provide this argument, we will just create a temporary file and give you the handle to that.
     * @returns file system handle
     */
    public function getFile($in_targetfilepath = null){

        $target_file = $in_targetfilepath;

        if(!$target_file){
            $target_file = tempnam(sys_get_temp_dir(), "Algo_");
        }

        $this->client->setOptions(['sink' => $target_file]);
        $this->getDataFile();
        $this->client->setOptions(['sink' => null]);
        return $target_file; 

    }

    /**
     * Call the server and create a DataFile object that represents this file
     */
    public function getDataFile(){

        $this->response = $this->client->doFileGet($this->connector, $this->path);

        //if there is a filesink provided (as in getFile), the result won't be set for the sake of memory
        if(is_null($this->client->getOptions()['sink'])){
            $this->result = $this->response->getBody()->getContents();
        }

        $this->last_modified = $this->response->getHeaders()['Content-Type'][0];
        $this->content_type = $this->response->getHeaders()['Content-Type'][0];
        $this->size = $this->response->getHeaders()['Content-Length'][0];

        return $this;
    }

    public function getLastModified(){
        if(!$this->result) $this->getDataFile();
        return $this->last_modified;
    }

    public function getSize(){
        if(!$this->result) $this->getDataFile();
        return $this->size;
    }

    public function getContentType(){
        if(!$this->result) $this->getDataFile();
        return $this->content_type;
    }

    public function getBytes(){
        if(!$this->result) $this->getDataFile();
        return $this->result;
    }

    public function getJson(){
        if(!$this->result) $this->getDataFile();
        $json_result = json_decode($this->result);
        
        if(is_null($json_result))
            throw new AlgoException("cannot convert result to json");
        
        return $json_result;
    }

    public function getString(){
        if(!$this->result) $this->getDataFile();
        return $this->result;
    }

    public function getFilename() {
        return $this->name;
    }

    public function put($in_input){
        $this->response = $this->client->doDataPut($this->connector, $this->path, $in_input);
        return $this;
    }

    public function putFile($in_filepath){
        $filepath = $in_filepath;

        if(!file_exists($filepath)){
            $filepath = getcwd().'/'.$in_filepath; //try the local directory, i.e. "./"
            if(!file_exists($filepath))
                throw new \Exception("file does not exist: ".$in_filepath." or " .$filepath);
        }

        $bin_file = file_get_contents($filepath);

        $this->response = $this->client->doDataPut($this->connector, $this->path, $bin_file);
        return $this;
    }

    public function putJson($in_input){
        $this->response = $this->client->doDataPut($this->connector, $this->path, json_encode($in_input));
        return $this;
    }

    public function delete(){
        $this->response = $this->client->doDataDelete($this->connector, $this->path);
        return $this;
    }

}