<?php

declare(strict_types=1);

final class ClientDataDirectoryTest extends BaseTest
{
    const HOME_DIR = "data://.my";
    
    public function testHomeDirectoryExists()
    {
        $client = $this->getClient();    
        $foo_dir = $client->dir(self::HOME_DIR); 
        $this->assertTrue($foo_dir->exists());
    }

    public function testDirectoryDoesNotExist()
    {
        $client = $this->getClient();
        $no_dir = $client->dir("data://.my/this_does_not_exist");
        $this->assertFalse($no_dir->exists());
    }


    public function testListDirectories()
    {
        $client = $this->getClient();
        $dirs = $client->dir('data://.my');
        $this->assertInternalType('array', $dirs->folders()); 
    }


    public function testConstructor(){
        $dir = new Algorithmia\DataDirectory("data://.my/foo");

        $this->assertEquals("foo",$dir->getName());
        $this->assertEquals(".my/foo",$dir->getPath());
        $this->assertEquals(".my",$dir->getParent());
        $this->assertEquals("data",$dir->getConnector());
    }

    public function testInvalidConnector(){
        $this->expectException(\Algorithmia\AlgoException::class);
        $dir = new Algorithmia\DataDirectory("invalid://.my/foo");
    }

    public function testEndingSlash(){
        $dir = new Algorithmia\DataDirectory("data://.my/foo/");

        $this->assertEquals("foo",$dir->getName());
        $this->assertEquals(".my/foo",$dir->getPath());
        $this->assertEquals(".my",$dir->getParent());
        $this->assertEquals("data",$dir->getConnector());
    }

    //this isn't supported as of yet in the actual API...
    public function testTwoLevelDirectory(){
        $dir = new Algorithmia\DataDirectory("s3://.my/foo/morefoo");

        $this->assertEquals("morefoo",$dir->getName());
        $this->assertEquals(".my/foo/morefoo",$dir->getPath());
        $this->assertEquals(".my/foo", $dir->getParent());
        $this->assertEquals("s3",$dir->getConnector());
    }

    public function testGetDataUrl(){
        $client = $this->getClient();
        $dir = new Algorithmia\DataDirectory("s3://.my/foo", $client); //sending in the client now
        $this->assertEquals("https://api.algorithmia.com/v1/connector/s3/.my/foo", $client->getDataUrl($dir->getConnector(),$dir->getPath()));
    }

    public function testGetDataAPIUrlAfterSetServer(){
        $client = $this->getClient();
        $client->setOptions(['server' => 'https://api2.algorithmia.com']);
        $dir = new Algorithmia\DataDirectory("data://.my/foo", $client); 
        $this->assertEquals("https://api2.algorithmia.com/v1/connector/data/.my/foo", $client->getDataUrl($dir->getConnector(),$dir->getPath()));
    }

    public function testCreateAndDeleteDataDirectory() {
        $client = $this->getClient();

        $newdir = $client->dir("data://.my/fooNew2")->create();

        $this->assertInstanceOf(\Algorithmia\DataDirectory::class, $newdir);

        //we can check the response also:
        $this->assertEquals("OK", $newdir->getResponse()->getReasonPhrase());
        $this->assertEquals(200, $newdir->getResponse()->getStatusCode());

        //check and see that dir now appears in our dir list!
        $dirs = $client->dir('data://.my');

        $this->assertTrue($dirs->containsFolder("fooNew2"));

        //also check exists
        $this->assertTrue($client->dir("data://.my/fooNew2")->exists());

        //clean up by deleting the folder.
        $newdir->delete();

        //we can check the response for the delete also:
        $this->assertEquals("OK", $newdir->getResponse()->getReasonPhrase());
        $this->assertEquals(200, $newdir->getResponse()->getStatusCode());

        //check and see that dir now appears in our dir list!
        $dirs = $client->dir('data://.my');

        $this->assertFalse($dirs->containsFolder("fooNew2"));

    }

    public function testDeleteWithForce() {
        $client = $this->getClient();
        
        $newdir = $client->dir("data://.my/fooNew2")->create();
        $this->assertTrue($newdir->exists());

        $response = $newdir->file("Secret.txt")->put("42");
        $this->assertEquals(200, $response->getStatusCode());

        //delete will fail because folder has contents
        $this->expectException(\Algorithmia\AlgoException::class);
        $newdir->delete();

        $this->assertTrue($newdir->exists());

        $newdir->delete(true);
        $this->assertFalse($newdir->exists());

    }
    
    //the api only returns 1000 records at a time, but our client should return them all.
    public function testListFilesWithPaging() {
        $client = $this->getClient();
        
        $num_files = 1100;

        //if "many_files" doesn't already exist we will create it but note: this takes 10 minutes or so (for me!)
        $newdir = $client->dir("data://.my/many_files"); 
        if(!$newdir->exists()){
            //create the dir and a bunch of files
            $newdir->create();
            for($i = 1; $i <= $num_files; $i++) {
                $newdir->file($i.".txt")->put("not quite empty file #".$i);
            }
        }
            
        $array_of_all_files = $newdir->files();

        $this->assertEquals(count($array_of_all_files), $num_files);

        //$newdir->delete(true); 

    }

}