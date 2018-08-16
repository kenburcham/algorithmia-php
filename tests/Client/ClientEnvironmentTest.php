<?php

final class ClientEnvironmentTest extends BaseTest
{
    const NOT_MY_API = "MPHNSD-548TMD";
    const NOT_BASEPATH = "https://api2.algorithmia.com";

    public function testCanConstructEmptyClient()
    {
        $client = new Algorithmia\Client();

        //all are defaults
        $this->assertInstanceOf(Algorithmia\Client::class, $client);
        $this->assertEquals($client->getOptions()['key'], null);
        $this->assertEquals($client->getOptions()['server'], Algorithmia\Client::API_BASE_PATH);

    }

    public function testCanSetClientOptions()
    {
        $client = new Algorithmia\Client(self::NOT_MY_API, self::NOT_BASEPATH);

        //all are intentionally set
        $this->assertInstanceOf(Algorithmia\Client::class, $client);
        $this->assertEquals($client->getOptions()['key'], self::NOT_MY_API);
        $this->assertEquals($client->getOptions()['server'], self::NOT_BASEPATH);

    }

    public function testCanGetEnvironmentVars()
    {
        putenv("ALGORITHMIA_API_KEY=".self::NOT_MY_API);
        putenv("ALGORITHMIA_API=".self::NOT_BASEPATH);

        $client = new Algorithmia\Client(); //no params sent

        //all are from environment vars
        $this->assertInstanceOf(Algorithmia\Client::class, $client);
        $this->assertEquals($client->getOptions()['key'], self::NOT_MY_API);
        $this->assertEquals($client->getOptions()['server'], self::NOT_BASEPATH);

        //clean up or else other tests will fail. :)
        putenv("ALGORITHMIA_API_KEY");
        putenv("ALGORITHMIA_API");

    }
}

