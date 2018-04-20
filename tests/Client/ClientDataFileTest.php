<?php

declare(strict_types=1);

final class ClientDataFileTest extends BaseTest
{
    const HOME_DIR = "data://.my";
    const FOOFILE = "data://.my/foo/foofile.txt";
    
    public function testConstructor(){
        $file = new Algorithmia\DataFile(self::FOOFILE);

        $this->assertEquals("foofile.txt",$file->getName());
        $this->assertEquals(".my/foo/foofile.txt",$file->getPath());
        $this->assertEquals(".my/foo",$file->getParent());
        $this->assertEquals("data",$file->getConnector());
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


}