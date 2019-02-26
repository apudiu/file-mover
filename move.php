<?php
require 'App/Process.php';

// $filesList = 'C:\Users\moham\Desktop\files.txt';
// $inputDir  = 'C:\Users\moham\Desktop\files';
// $recursive = 'y';

echo LE."Files list location: ";
$filesList = trim(fgets(STDIN));

echo LE;

echo "Files directory location: ";
$inputDir = trim(fgets(STDIN));

echo LE;

echo "Copy or Move  [c/m]: ";
$copy = trim(fgets(STDIN));

echo LE;

echo "Recursive? [y/n]: ";
$recursive = trim(fgets(STDIN));

echo LE;


$m = new Process($filesList, $inputDir, $recursive, $copy);

// echo var_dump($m->directoryContentList);
