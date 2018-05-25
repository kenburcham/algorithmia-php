<?php
error_reporting(E_ALL); ini_set('display_errors', 1);

include "../vendor/autoload.php";

use Algorithmia;
$client = Algorithmia::client('API_KEY');
$localFile = "/home/ken/gitprojects/algorithmia-php/examples/assets/todo_list.txt";
$dataDir = "data://.my/foo";
$testFile = $dataDir."/testFileDeleteme.txt";
$missingFile = $dataDir."/thisFileDoesNotExist.txt";

$testFileH = $client->file($testFile);

$foo = $client->dir($dataDir);

$testFileH->putFile($localFile);

$foo = $client->dir($dataDir);
foreach($foo->files() as $file) {
    echo "\n\nFile: ".$file->getPath();
    echo "\n - exists: ".$file->exists();
    echo "\n - string: ".$file->getString();
    echo "\n - bytes: ".$file->getBytes();
    echo "\n - json: ".$file->getJson();
    echo "\n - contents: ".$file->getFile();
}
/*

$testFileH->putJson([1,2,3,4]);

$foo = $client->dir($dataDir);
foreach($foo->files() as $file) {
    echo "\n\nFile: ".$file->getPath();
    echo "\n - exists: ".$file->exists();
    echo "\n - string: ".$file->getString();
    echo "\n - bytes: ".$file->getBytes();
    echo "\n - json: ".$file->getJson();
    echo "\n - contents: ".file_get_contents($file->getFile());
}


$testFileH->put("Delete Me, just a test.");

$foo = $client->dir($dataDir);
foreach($foo->files() as $file) {
    echo "\n\nFile: ".$file->getPath();
    echo "\n - exists: ".$file->exists();
    echo "\n - string: ".$file->getString();
    echo "\n - bytes: ".$file->getBytes();
    echo "\n - json: ".$file->getJson();
    echo "\n - contents: ".file_get_contents($file->getFile());
}
*/
//$testFileH->delete();

echo "\nmissing file exists:
".($client->file($missingFile)->exists()?'Y':'N');

try {
$client->file($missingFile)->getFile();
echo "\n\nOh no! Exception not thrown!";
} catch (Algorithmia\AlgoException $x) {
echo "\n\AlgoException properly thrown for 404";
}

?>