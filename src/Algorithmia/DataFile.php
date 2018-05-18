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

    public function getFile(){
        try{
            $this->response = $this->client->doFileGet($this->connector, $this->path);
        }
        catch(\Exception $e)
        {
            $error = json_decode($e->getResponse()->getBody()->getContents())->error;
            throw new AlgoException($error->message);
        }

        $this->result = $this->response->getBody()->getContents();
        $this->last_modified = $this->response->getHeaders()['Content-Type'][0];
        $this->content_type = $this->response->getHeaders()['Content-Type'][0];
        $this->size = $this->response->getHeaders()['Content-Length'][0];
        
        return $this;
    }

    public function getLastModified(){
        return $this->last_modified;
    }

    public function getFilename() {
        return $this->name;
    }

    public function getSize(){
        return $this->size;
    }

    public function getContentType(){
        return $this->content_type;
    }

    public function getBytes(){
        return $this->result;
    }

    public function getJson(){
        return json_decode($this->result);
    }

    public function getString(){
        return $this->result;
    }

    public function put($in_input){
        try{    
            $this->response = $this->client->doDataPut($this->connector, $this->path, $in_input);
        }
        catch(\Exception $e)
        {
            $error = json_decode($e->getResponse()->getBody()->getContents())->error;
            throw new AlgoException($error->message);
        }
        return $this->response;
    }

    public function putFile($in_filepath){
        if(!file_exists($in_filepath))
            throw new \Exception("file does not exist: ".$in_filepath);

        $bin_file = file_get_contents($in_filepath);

        try{
            $this->response = $this->client->doDataPut($this->connector, $this->path, $bin_file);
        }
        catch(\Exception $e)
        {
            $error = json_decode($e->getResponse()->getBody()->getContents())->error;
            throw new AlgoException($error->message);
        }
        return $this->response;
    }

    public function putJson($in_input){
        try{
            $this->response = $this->client->doDataPut($this->connector, $this->path, json_encode($in_input));
        }
        catch(\Exception $e)
        {
            $error = json_decode($e->getResponse()->getBody()->getContents())->error;
            throw new AlgoException($error->message);
        }

        return $this->response;
    }

    public function delete(){
        try{
            $this->response = $this->client->doDataDelete($this->connector, $this->path);
        }
        catch(\Exception $e)
        {
            $error = json_decode($e->getResponse()->getBody()->getContents())->error;
            throw new AlgoException($error->message);
        }

        return $this->response;
    }

}