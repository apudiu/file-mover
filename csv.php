<?php
require_once('App/MoveFileToDir.php');

echo LE."CSV file: ";
$csvFile = trim(fgets(STDIN));

echo LE."Copied / moved dir: ";
$inDir = trim(fgets(STDIN));

new MoveFileToDir($csvFile, $inDir);

