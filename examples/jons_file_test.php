<?php
error_reporting(E_ALL); ini_set('display_errors', 1);

include "../vendor/autoload.php";

$client = Algorithmia::client('API_KEY');
$localFile = "/home/ken/gitprojects/algorithmia-php/examples/assets/todo_list.txt";
$dataDir = "data://.my/foo_php";
$testFile = $dataDir."/testFileDeleteme.txt";
$missingFile = $dataDir."/thisFileDoesNotExist.txt";

$testFileH = $client->file($testFile);

$foo = $client->dir($dataDir);

if(!$foo->exists())
    $foo->create();

$testFileH->putFile($localFile);

//note: these would just overwrite the previous file each time... lets make new ones.
//$testFileH->putJson([1,2,3,4]);  
//$testFileH->put("Delete Me, just a test.");

$foo->file("jsonfileDeleteme.json")->putJson([1,2,3,4]);
$foo->file("new.txt")->put("Delete Me, just a test.");

// you can call this to setup the dirobj again but it isn't necessary...
//$foo = $client->dir($dataDir); 

foreach($foo->files() as $file) {
    echo "\n\nFile: ".$file->getPath();
    echo "\n - exists: ".$file->exists();
    echo "\n - string: ".$file->getString();
    echo "\n - bytes: ".$file->getBytes();
    echo "\n - json: ".json_encode($file->getJson());
    $temp_file = $file->getFile(); //writes a temp file in the temp directory. you can also specify the /path/to/file if you want
    echo "\n - tempfile: " .$temp_file;
    echo "\n - contents: ". file_get_contents($temp_file);
    unlink($temp_file); //always nice to clean up.
    echo "\n -------------------------- \n\n";
}


$foo->delete(true);

echo "\nmissing file exists:
".($client->file($missingFile)->exists()?'Y':'N');

try {
$client->file($missingFile)->getFile();
echo "\n\nOh no! Exception not thrown!";
} catch (Algorithmia\AlgoException $x) {
echo "\n\AlgoException properly thrown for 404";
}

?>