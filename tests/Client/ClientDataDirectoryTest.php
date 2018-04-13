<?php

declare(strict_types=1);

final class ClientDataDirectoryTest extends BaseTest
{
    //basic text algorithms
    const EXISTING_DIR = "data://.my/foo";

    /*
    public function testListDirectoryContents()
    {
        $client = $this->getClient();
        
        $foo_dir = $client->dir(EXISTING_DIR); //must already exist and have only one file.

        $this->assertCount(1,$foo_dir->files());
    }
    */


    public function testConstructor(){
        $dir = new Algorithmia\DataDirectory("data://.my/foo");

        $this->assertEquals("foo",$dir->getName());
        $this->assertEquals(".my/foo",$dir->getPath());
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
        $this->assertEquals("data",$dir->getConnector());
    }

    public function testTwoLevelDirectory(){
        $dir = new Algorithmia\DataDirectory("s3://.my/foo/morefoo");

        $this->assertEquals("morefoo",$dir->getName());
        $this->assertEquals(".my/foo/morefoo",$dir->getPath());
        $this->assertEquals("s3",$dir->getConnector());
    }
}