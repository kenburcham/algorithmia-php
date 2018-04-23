<?php

declare(strict_types=1);

final class ClientDataFileTest extends BaseTest
{
    const HOME_DIR = "data://.my";
    const FOODIR = "data://.my/foo";
    const FOOFILE = "data://.my/foo/foofile.txt";
    const EXAMPLE_FILE = "test_example.txt"; //text file present in the test directory
    const EXAMPLE_BIN_FILE = "opencv_example.png"; //binary file in the test directory
    
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

    public function testPutGetDeleteJsonFile(){
        $client = $this->getClient();

        $response = $client->file("data://.my/foo/Optimus_Prime.json")->putJson(["faction" => "Autobots"]);
        $this->assertEquals(200, $response->getStatusCode());

        $file_json = $client->file("data://.my/foo/Optimus_Prime.json")->getFile()->getJson();

        $this->assertEquals("Autobots",$file_json->faction);

        $client->file("data://.my/foo/Optimus_Prime.json")->delete();
    }

    public function testPutGetDeleteTextFile(){
        $client = $this->getClient();

        $file = $client->file("data://.my/foo/Optimus_Prime.txt");
        $file->put("Leader of the Autobots");
        $this->assertEquals(200, $file->getResponse()->getStatusCode());

        $file_string_from_server = $client->file("data://.my/foo/Optimus_Prime.txt")->getFile()->getString();

        $this->assertEquals("Leader of the Autobots",$file_string_from_server);

        $file->delete();
    }


    public function testPutGetDeleteBinaryFile(){
        $client = $this->getClient();
        $bin_file = $this->testDir . '/'. self::EXAMPLE_BIN_FILE;

        $response = $client->file("data://.my/foo/opencv_test.png")->putFile($bin_file);
        $this->assertEquals(200, $response->getStatusCode());

        $file = $client->file("data://.my/foo/opencv_test.png")->getFile();
        $this->assertEquals(200, $file->getResponse()->getStatusCode());
        $this->assertEquals("image/png", $file->getContentType());
        $this->assertEquals(275091 , $file->getSize());

        $local_file = $this->testDir . '/from_server.png';

        //if you want to write the file out to the file system you can: 
        file_put_contents($local_file, $file->result);
        $this->assertTrue(file_exists($local_file));
        unlink($local_file);

        $response = $client->file("data://.my/foo/opencv_test.png")->delete();
        $this->assertEquals(200, $response->getStatusCode());
    }

    //save a file using just the filepath. use the filename as the algo file.
    public function testDirPutFile(){
        $client = $this->getClient();
        $foo_dir = $client->dir(self::FOODIR);
        $response = $foo_dir->putFile($this->testDir . '/'. self::EXAMPLE_FILE); // like: /testdir/test_example.txt
        $this->assertEquals(200, $response->getStatusCode());
        $client->file(self::FOODIR.'/'.self::EXAMPLE_FILE)->delete();
    }

}