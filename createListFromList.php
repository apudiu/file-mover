<?php
require 'App/GenerateFilesListFromList.php';

// list.txt file
echo LE."List location: ";
$list = trim(fgets(STDIN));

echo LE;


// /home/apu/Desktop/files
echo "Files directory: ";
$inDir = trim(fgets(STDIN));

echo LE;


$m = new GenerateFilesListFromList($list, $inDir);

// echo var_dump($m->directoryContentList);
