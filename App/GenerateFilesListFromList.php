<?php 

define('DS', DIRECTORY_SEPARATOR);
define('LE', PHP_EOL);


class GenerateFilesListFromList {

    /**
     * Provide list files
     * @var array
     */
	private $inListFiles = [];

    /**
     * Output list file name
     * @var string
     */
	private $outListName = 'list_filtered.txt';

    /**
     * Full path of output list file
     * @var string
     */
	private $outList;



	public function __construct($list, $inDir) {

	    // checking if provided list is a valid file
        if (!is_file($list)) {
            die("'$list' is not a file or did not exists.".LE);
        }

        // processing provided list file
        $this->prepareInList($list);


        // setting files list location
        $this->outList = $inDir.DS.$this->outListName;



		// Scanning input directory
		$directoryContents = $this->directoryScan($inDir);

		// filtering files using regExp
		$filteredFileNames = $this->filterFileNames($directoryContents);
		

		// writing filtered contents to the file
		$this->fileWriter($filteredFileNames);

	}


    /**
     * Puts list file entries to array
     * @param $listFile
     */
	public function prepareInList($listFile) {

        $f = fopen($listFile, 'r');

        while (!feof($f)) {

            $line = trim(fgets($f));

            if (!empty($line)) {
                $this->inListFiles[] = trim($line);
            }
        }
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
	 * @param  array $fileNames Files full path
	 * @return array            Filtered files list
	 */
	private function filterFileNames($fileNames) {

		if (!is_array($fileNames)) {
			die("File names should be an array");
		}

		if (empty($fileNames)) {
			die("There is no file in the files list");
		}

		$filteredList = [];

        foreach ($this->inListFiles as $fileName) {

            $filteredList[] = preg_grep('/'.$fileName.'/', $fileNames);
        }

        return $this->arrayFlatten($filteredList);
	}


	/**
	 * Writes a file, If file doesn't exists file will be created
	 * @param  string $file     file location
	 * @param  array $contents contents to be written to the file
	 * @return boolean           returns true on success
	 */
	private function fileWriter($contents) {

	    $file = $this->outList;

		$f = fopen($file, 'w');

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

    /**
     * Flatten array
     * @param array $array
     * @return array
     */
	private function arrayFlatten(array $array = []) {
        $result = [];

        if (!is_array($array)) {
            $array = func_get_args();
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->arrayFlatten($value));
            } else {
                $result = array_merge($result, [$key => $value]);
            }
        }

        return $result;
    }
}
