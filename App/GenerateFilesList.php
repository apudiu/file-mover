<?php 

define('DS', DIRECTORY_SEPARATOR);
define('LE', PHP_EOL);


class GenerateFilesList {

	private $regEx;
	private $listLocation;
	private $listName = 'list.txt';



	public function __construct($regEx, $inDir) {

		// Scanning input directory

		$directoryContents = $this->directoryScan($inDir);
		

		// setting files list location
		
		$this->inDir = $inDir.DIRECTORY_SEPARATOR.$this->listName;


		// filtering files using regExp

		$filteredFileNames = $this->filterFileNames($regEx, $directoryContents);
		

		// writing filtered contents to the file
		
		$this->fileWriter($this->inDir, $filteredFileNames);


	}


	/**
	 * Scans & process directory contents to work with
	 *
	 * @param $dir string	location of the working directory
	 * @return array
	 */
	private function directoryScan($dir) {
		
		// check for the validity of input / working directory
		
		if (!is_dir($dir)) {
			die("'$dir' is not a directory.".LE);
		}
		
		// listing directory contents
		
		$result = [];
		
		$root = scandir($dir);
		foreach($root as $value)
		{
			// removing dots & output directory
			
			if($value === '.' || $value === '..') {
				continue;
			}
			
			// listing only files
			
			if(is_file("$dir".DS."$value")) {
				$result[$value]="$dir".DS."$value";
				continue;
			}
			
			
			// recursive call to self(this method) so we can get files listing recursively
			
			foreach($this->directoryScan("$dir".DS."$value") as $value1)
			{
				$result[basename($value1)]=$value1;
			}
		}
		
		return $result;
	}


	/**
	 * Filters an array
	 * @param  string $regEx     filter string in form of Regular Expression
	 * @param  array $fileNames Files full path
	 * @return array            Filtered files list
	 */
	private function filterFileNames($regEx, $fileNames) {

		if (!is_array($fileNames)) {
			die("File names should be an array");
		}

		if (empty($fileNames)) {
			die("There is no file in the files list");
		}

		return preg_grep('/'.$regEx.'/', $fileNames);

	}


	/**
	 * Writes a file, If file doesn't exists file will be created
	 * @param  string $file     file location
	 * @param  array $contents contents to be written to the file
	 * @return boolean           returns true on success
	 */
	private function fileWriter($file, $contents) {

		$f = fopen($file, 'a');

		$filesCount = 1;

		foreach ($contents as $location => $fileName) {
			
			fwrite($f, basename($fileName).PHP_EOL);

			echo "#{$filesCount} {$fileName} has been added.".PHP_EOL;

			$filesCount++;
		}

		fclose($f);

		echo "Filtered files list created in: {$file}".PHP_EOL;

		return true;
	}
}
