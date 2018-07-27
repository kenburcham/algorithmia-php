<?php

final class ClientJsonTest extends BaseTest
{
    //json algorithms to test
    const ALGORITHM_LISTANAGRAMS = "WebPredict/ListAnagrams/0.1.0";
    const ALGORITHM_AUTOTAG = "nlp/AutoTag/1.0.1";
    const ALGORITHM_ANALYZEURL = "web/AnalyzeURL/0.2.17";

    public function testArrayInArrayOut_Anagrams()
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

    public function testTextInArrayOut_Autotag()
    {
        $client = $this->getClient();
        $algo = $client->algo(self::ALGORITHM_AUTOTAG);
        $str_example = "A purely peer-to-peer version of electronic cash would allow online payments to be sent directly from one party to another without going through a financial institution. Digital signatures provide part of the solution, but the main benefits are lost if a trusted third party is still required to prevent double-spending. We propose a solution to the double-spending problem using a peer-to-peer network. The network timestamps transactions by hashing them into an ongoing chain of hash-based proof-of-work, forming a record that cannot be changed without redoing the proof-of-work. The longest chain not only serves as proof of the sequence of events witnessed, but proof that it came from the largest pool of CPU power. As long as a majority of CPU power is controlled by nodes that are not cooperating to attack the network, they'll generate the longest chain and outpace attackers. The network itself requires minimal structure. Messages are broadcast on a best effort basis, and nodes can leave and rejoin the network at will, accepting the longest proof-of-work chain as proof of what happened while they were gone.";
        $tags = $algo->pipe($str_example)->result;
        
        $this->assertCount(8,$tags);
    }

    public function testArrayInObjectOut_AnalyzeURL(){
        $client = $this->getClient();
        $algo = $client->algo(self::ALGORITHM_ANALYZEURL);

        $url_to_analyze = ["https://algorithmia.com/algorithms/weka/DigitRecognition", "weka"];

        $obj_result = $algo->pipe($url_to_analyze)->result;

        $this->assertEquals($obj_result->url, "https://algorithmia.com/algorithms/weka/DigitRecognition");
        $this->assertTrue($obj_result->marker);

    }

    public function testJsonExceptionIfNotJson(){
        $client = $this->getClient();
        $foo_dir = $client->dir("data://.my/".TEST_DIR_NAME);
        $foo_dir->create();

        $the_file = $foo_dir->file("myfile.txt")->put("Leader of the Autobots");

        $this->expectException(\Algorithmia\AlgoException::class);
        $the_file->getJson();

        $foo_dir->delete(true);

    }
}