<?php
declare(strict_types=1);

final class ClientTest extends BaseTest
{
    const ALGORITHM_EXAMPLE_TEXT = "util/Echo/0.2.1";

    public function testCanGetClient()
    {
        $client = $this->getClient();
        $this->assertInstanceOf(Algorithmia\Client::class, $client);
    }

    public function testCanGetAlgorithmFromClient()
    {
        $client = $this->getClient();
        $algo = $client->algo(self::ALGORITHM_EXAMPLE_TEXT);
        $this->assertInstanceOf(Algorithmia\Algorithm::class, $algo);
    }

    public function testCanGetAlgorithmFromStaticNoAPI()
    {
        $algo = Algorithmia::algo(self::ALGORITHM_EXAMPLE_TEXT);
        $this->assertInstanceOf(Algorithmia\Algorithm::class, $algo);
    }

    public function testCallTextAlgorithm()
    {
        $client = $this->getClient();
        $algo = $client->algo(self::ALGORITHM_EXAMPLE_TEXT);

        $string_to_test = "PHP-Test";

        $response = $algo->pipe($string_to_test);

        $this->assertEquals($response->result, $string_to_test);
    }
}