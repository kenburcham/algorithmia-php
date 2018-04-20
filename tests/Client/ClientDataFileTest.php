<?php

declare(strict_types=1);

final class ClientDataFileTest extends BaseTest
{
    const HOME_DIR = "data://.my";
    const FOOFILE = "data://.my/foo/foofile.txt";
    const EXAMPLE_FILE = "test_example.txt"; //file exists in test directory
    
    public function testConstructor(){
        $file = new Algorithmia\DataFile(self::FOOFILE);

        $this->assertEquals("foofile.txt",$file->getName());
        $this->assertEquals(".my/foo/foofile.txt",$file->getPath());
        $this->assertEquals(".my/foo",$file->getParent());
        $this->assertEquals("data",$file->getConnector());
    }

    public function testCreateFooIfNecessary(){
        $client = $this->getClient();
        
        $foo_file = $client->file(self::FOOFILE);
        $foo_dir = $foo_file->parent();

        if(!$foo_dir->exists())
            $foo_dir->create();
        
        $this->assertTrue($foo_dir->exists());
    }

    public function testClientFile(){
        $client = $this->getClient();    
        $file = $client->file(self::FOOFILE);
        $this->assertInstanceOf(Algorithmia\DataFile::class, $file);
    }

    public function testDirFile(){
        $client = $this->getClient();  
        $file = $client->dir(self::HOME_DIR)->file(self::FOOFILE);
        $this->assertInstanceOf(Algorithmia\DataFile::class, $file);
    }

    public function testPutDeleteFile(){
        $client = $this->getClient();
        $file = $client->file(self::FOOFILE);

        $this->assertFalse($file->exists());
        $this->assertEquals(404, $file->getResponse()->getStatusCode());

        $bin_file = $this->testDir . '/'. self::EXAMPLE_FILE;

        $response = $client->file(self::FOOFILE)->putFile($bin_file);

        //did it work? two ways to tell... this is fast
        $this->assertEquals($response->getStatusCode(), 200);
        
        //this is slow, but verifies via server call.
        $this->assertTrue($file->exists());

        //now lets clean up
        $response = $file->delete();
        $this->assertEquals("OK", $file->getResponse()->getReasonPhrase());
        $this->assertEquals(200, $file->getResponse()->getStatusCode());

        $this->assertFalse($client->file(self::FOOFILE)->exists());
    }

    public function testPutJsonFile(){
        $client = $this->getClient();

        $response = $client->file("data://.my/foo/Optimus_Prime.json")->putJson(["faction" => "Autobots"]);
        $this->assertEquals(200, $response->getStatusCode());

        $client->file("data://.my/foo/Optimus_Prime.json")->delete();
    }

    public function testPutTextFile(){
        $client = $this->getClient();

        $file = $client->file("data://.my/foo/Optimus_Prime.txt");
        $file->put("Leader of the Autobots");
        $this->assertEquals(200, $file->getResponse()->getStatusCode());

        $file->delete();
    }


}