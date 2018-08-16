<?php

final class ClientDataFileTest extends BaseTest
{
    const HOME_DIR = "data://.my";
    const FOODIR = "data://.my/".TEST_DIR_NAME;
    const FOOFILE = "data://.my/".TEST_DIR_NAME."/foofile.txt";
    const EXAMPLE_FILE = "test_example.txt"; //text file present in the test directory
    const EXAMPLE_BIN_FILE = "opencv_example.png"; //binary file in the test directory

    public function testConstructor(){
        $file = new Algorithmia\DataFile(self::FOOFILE);

        $this->assertEquals("foofile.txt",$file->getName());
        $this->assertEquals(".my/".TEST_DIR_NAME."/foofile.txt",$file->getPath());
        $this->assertEquals(".my/".TEST_DIR_NAME,$file->getParent());
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

        $bin_file = $this->testDir . '/'. self::EXAMPLE_FILE;

        $file = $client->file(self::FOOFILE)->putFile($bin_file);

        //did it work? two ways to tell... this is fast
        $this->assertEquals($file->response->getStatusCode(), 200);
        
        //this is slow, but verifies via server call.
        $this->assertTrue($file->exists());

        //now lets clean up
        $file = $file->delete();
        $this->assertEquals("OK", $file->response->getReasonPhrase());
        $this->assertEquals(200, $file->response->getStatusCode());

        $this->assertFalse($client->file(self::FOOFILE)->exists());
    }

    public function testPutGetDeleteJsonFile(){
        $client = $this->getClient();

        $file = $client->file("data://.my/".TEST_DIR_NAME."/Optimus_Prime.json")->putJson(["faction" => "Autobots"]);
        $this->assertEquals(200, $file->response->getStatusCode());

        $file_json = $client->file("data://.my/".TEST_DIR_NAME."/Optimus_Prime.json")->getJson();

        $this->assertEquals("Autobots",$file_json->faction);

        $client->file("data://.my/".TEST_DIR_NAME."/Optimus_Prime.json")->delete();
    }

    public function testPutGetDeleteTextFile(){
        $client = $this->getClient();

        $file = $client->file("data://.my/".TEST_DIR_NAME."/Optimus_Prime.txt");
        $file->put("Leader of the Autobots");
        $this->assertEquals(200, $file->response->getStatusCode());

        $file_string_from_server = $client->file("data://.my/".TEST_DIR_NAME."/Optimus_Prime.txt")->getString();

        $this->assertEquals("Leader of the Autobots",$file_string_from_server);

        $file->delete();
    }


    public function testPutGetDeleteBinaryFile(){
        $client = $this->getClient();
        $bin_file = $this->testDir . '/'. self::EXAMPLE_BIN_FILE;

        $file = $client->file("data://.my/".TEST_DIR_NAME."/opencv_test.png")->putFile($bin_file);
        $this->assertEquals(200, $file->response->getStatusCode());

        $file = $client->file("data://.my/".TEST_DIR_NAME."/opencv_test.png")->getDataFile();
        $this->assertEquals(200, $file->response->getStatusCode());
        $this->assertEquals("image/png", $file->getContentType());
        $this->assertEquals(275091 , $file->getSize());

        $local_file = $this->testDir . '/from_server.png';

        //if you want to write the file out to the file system you can: 
        file_put_contents($local_file, $file->result);
        $this->assertTrue(file_exists($local_file));
        unlink($local_file);

        $file = $client->file("data://.my/".TEST_DIR_NAME."/opencv_test.png")->delete();
        $this->assertEquals(200, $file->response->getStatusCode());
    }

    //save a file using just the filepath. use the filename as the algo file.
    public function testDirPutFile(){
        $client = $this->getClient();
        $foo_dir = $client->dir(self::FOODIR);
        $file = $foo_dir->putFile($this->testDir . '/'. self::EXAMPLE_FILE); // like: /testdir/test_example.txt
        $this->assertEquals(200, $file->response->getStatusCode());
        $client->file(self::FOODIR.'/'.self::EXAMPLE_FILE)->delete();
    }

    public function testPutNonExistentFile(){
        $client = $this->getClient();
        $foo_dir = $client->dir(self::FOODIR);
        $this->expectException(\Exception::class);
        $file = $foo_dir->putFile($this->testDir . '/NotHere.txt'); 
    }

    //our tests dir is our running dir - we should be able to put a file just by name: "opencv_example.png"
    public function testPutBareNamedFile(){ 
        $client = $this->getClient();
        $foo_dir = $client->dir(self::FOODIR);
        if(!$foo_dir->exists())
            $foo_dir->create();
        
        $file = $foo_dir->putFile('README.md');  //exists in ./ if you run the tests from the root algorithmia dir
        
        $this->assertEquals(200, $file->response->getStatusCode());
        $file->delete(true);
    }

    //now this actually gets a file handle... a temp file will be created via stream
    public function testGetFileSystemFile(){
        $client = $this->getClient();
        $foo_dir = $client->dir(self::FOODIR);
        $file = $foo_dir->putFile($this->testDir . '/'. self::EXAMPLE_FILE); // like: /testdir/test_example.txt
        $file->put("Leader of the Autobots");
        $this->assertEquals(200, $file->response->getStatusCode());

        //write the file to a temp dir and get the path to this file on the filesystem
        $my_file_path = $file->getFile();

        //file should exist now!
        $this->assertTrue(file_exists($my_file_path));

        //what is in the contents of the file? should be what we saved!
        $this->assertEquals("Leader of the Autobots", file_get_contents($my_file_path));
        
        //delete the file and the local file
        $file->delete();
        unlink($my_file_path);
    }

    //you can also specify the filesink that you want the response written to. this is handled with streams
    // so should work for very big files, too.
    public function testGetSpecifiedFileSystemFile(){
        $client = $this->getClient();
        $foo_dir = $client->dir(self::FOODIR);
        $file = $foo_dir->putFile($this->testDir . '/'. self::EXAMPLE_FILE); // like: /testdir/test_example.txt
        $file->put("Leader of the Autobots");
        $this->assertEquals(200, $file->response->getStatusCode());

        $my_filesink = $this->testDir . '/my_filesink.txt';

        //write the file to a SPECIFIED dir and get the path to this file on the filesystem
        $my_file_path = $file->getFile($my_filesink);

        //file should exist now!
        $this->assertTrue(file_exists($my_file_path));

        //what is in the contents of the file? should be what we saved!
        $this->assertEquals("Leader of the Autobots", file_get_contents($my_file_path));
        
        //delete the folder, file and the local file
        $foo_dir->delete(true);
        unlink($my_file_path);

    }


}