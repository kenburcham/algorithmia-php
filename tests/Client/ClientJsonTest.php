<?php
declare(strict_types=1);

final class ClientJsonTest extends BaseTest
{
    //json algorithms to test
    const ALGORITHM_LISTANAGRAMS = "WebPredict/ListAnagrams/0.1.0";

    public function testListAnagrams()
    {
        $client = $this->getClient();
        
        $algo = $client->algo(self::ALGORITHM_LISTANAGRAMS);

        $json_to_test = ["transformer", "terraforms", "retransform"];

        $response = $algo->pipe($json_to_test);

        $this->assertEquals(json_encode($response->result), "[\"transformer\",\"retransform\"]");

        //and we expect the fluent way to work, too
        $anagrams = $algo->pipe(["transformer", "terraforms", "retransform"])->result;

        $this->assertEquals("retransform",$anagrams[1]);
    }
}