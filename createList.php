<?php
require 'App/GenerateFilesList.php';

// \w[E]

echo LE."Files filtering (regular)expression: ";
$regEx = trim(fgets(STDIN));

echo LE;


// /home/apu/Desktop/files
echo "Files directory: ";
$inDir = trim(fgets(STDIN));

echo LE;


$m = new GenerateFilesList($regEx, $inDir);

// echo var_dump($m->directoryContentList);
