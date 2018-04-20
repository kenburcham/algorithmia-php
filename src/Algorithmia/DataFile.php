<?php

namespace Algorithmia;

class DataFile extends DataObject {

    private $last_modified;
    private $size;

    public function getFile(){

        
    }

    public function getBytes(){

    }

    public function getJson(){

    }

    public function getString(){

    }

    public function put($in_data){
        
    }

    public function putFile($in_filepath){
        if(!file_exists($in_filepath))
            throw new \Exception("file does not exist: ".$in_filepath);

        $bin_input = file_get_contents($in_filepath);

        $this->response = $this->client->doDataPut($this->connector, $this->path, $bin_input);
        return $this->response;
    }

    public function putJson($in_data){

    }

    public function delete(){
        $this->response = $this->client->doDataDelete($this->connector, $this->path);
        return $this;
    }

}