<?php
error_reporting(E_ALL); ini_set('display_errors', 1); 

include "../vendor/autoload.php";

//use Algorithmia;
$client = Algorithmia::client('API_KEY');

$myUsername = "jpeck";

$client->setOptions(['stdout' => true]);

$algo = $client->algo($myUsername.'/HelloWorld');
echo "\nHelloWorld: ".json_encode($algo->pipe("Foobar"));


$client->setOptions(['timeout' => 55, 'stdout' => false]);

$algo = $client->algo('opencv/SmartThumbnail/0.1');
$fileBytes = new Algorithmia\ByteArray(file_get_contents("/home/ken/gitprojects/algorithmia-php/examples/assets/cows.jpg"));
echo "\nSmartThumbnail: ".substr($algo->pipe($fileBytes)->result,0,5);

$client->setOptions(['output' => "raw", 'stdout' => false]);

$algo = $client->algo('WebPredict/ListAnagrams/0.1.0');
echo "\nListAnagrams: ".json_encode($algo->pipe(["transformer", "terraforms", "retransform"]));


try {
	$algo = $client->algo('demo/ThisAlgoDoesNotExist/');
	echo json_encode($algo->pipe("Foobar"));
} catch (Algorithmia\AlgoException $x) {
	echo "\nAlgoException properly thrown for 404";
}

?>