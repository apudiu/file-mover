<?php
/**
 * Created by PhpStorm.
 * User: Mohammad Ali Apu
 * Date: 1/19/2018
 * Time: 12:38 AM
 */

define('DS', DIRECTORY_SEPARATOR);
define('LE', PHP_EOL);

class Process {

	/**
	 * Input directory where files ready to move
	 *
	 * @var string
	 */
	public $inDir;
	
	/**
	 * Output directory where moved files will be placed & that's name
	 *
	 * @var string
	 */
	public $outDir,
		   $outDirName = 'MovedFiles';
	
	/**
	 * Whether move files recursively or not
	 *
	 * @var bool
	 */
	public $recursive;

    /**
     * Determines whether to copy file or move
     * @var string
     */
	public $copy;
	
	/**
	 * Files move list (txt) files contents or
	 * File names to move
	 *
	 * @var array
	 */
	public $filesList = [];
	
	/**
	 * Input directory contents
	 *
	 * @var array
	 */
	public $directoryContentList = [];
	
	
	
	
	public function __construct($filesList, $inputDir, $recursive, $copy='m') {
		
		// Setting recursive mode
		if ($recursive == 'y' || $recursive == 'Y') {
			$this->recursive = true;
		} else {
			$this->recursive = false;
		}

        // Setting copy or move mode
        if ($copy == 'm') {
            $this->copy = false;
        } else {
            $this->copy = true;

            // renaming out dir name if copying
            $this->outDirName = 'CopiedFiles';
        }
		
		// Setting in directory
		if (!is_dir($inputDir)) {
			die("Invalid input directory '$inputDir'".LE);
		}
		
		$this->inDir = $inputDir;
		// Setting out directory
		$this->outDir = $inputDir.DS.$this->outDirName;
		
		// Making output directory
		$this->makeOutDir();
		
		// Getting files list from text file
		$this->getFilesList($filesList);
		
		// Getting input directory contents
		$this->getDirectoryContents();
		
		// Moving files
		$this->moveFiles();
	}
	
	/**
	 * Makes directory where moved files will be saved
	 */
	private function makeOutDir() {
		
		if (file_exists($this->outDir)) {
			
			echo LE."Warning directory '$this->outDirName' already exists, files in it might be replaced.".LE;
			
		} else {
			
			if (!@mkdir($this->outDir)) {
				
				die("Output directory '$this->outDirName' creation failed, create it manually in '$this->inDir'");
			}
		}
	}
	
	
	/**
	 * Prepares list of file names to move from given text file
	 *
	 * @param $listFile string	location of the files list to move
	 */
	private function getFilesList($listFile) {
		
		// check for the validity of the files list (text file)
		
		if (!is_file($listFile)) {
			die("'$listFile' is not a file or did not exists.".LE);
		}
		
		// reading files content line by line
		
		$f = fopen($listFile, 'r');
		
		while (!feof($f)) {
			
			$line = trim(fgets($f));
			
			if (!empty($line)) {
				$this->filesList[] = $line;
			}
		}

        if ($this->copy) {
            echo LE.count($this->filesList).' files has to be copied'.LE;
		} else {
            echo LE.count($this->filesList).' files has to be moved'.LE;
        }

	}
	
	
	/**
	 * Prepares input directory contents for processing
	 */
	private function getDirectoryContents() {
		
		$this->directoryContentList = $this->directoryScan($this->inDir);
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
			
			if($value === '.' || $value === '..' || $value === $this->outDirName) {
				continue;
			}
			
			// listing only files
			
			if(is_file("$dir".DS."$value")) {
				$result[$value]="$dir".DS."$value";
				continue;
			}
			
			// recursive scan switch
			
			if (!$this->recursive) {
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
	 * Moves files
	 */
	private function moveFiles() {
		
		// file moved count
		
		$moved = 0;
		
		foreach ($this->filesList as $fileName) {
			
			// only move file if that is requested in files list
			
			if (array_key_exists($fileName, $this->directoryContentList)) {

                if ($this->copy) {

                    if (copy($this->directoryContentList[$fileName], $this->outDir.DS.$fileName)) {
                        echo "Copied: $fileName".LE;
                    } else {
                        echo "FILE COPY ERROR: $fileName".LE;
                    }

			    } else {

                    if (rename($this->directoryContentList[$fileName], $this->outDir.DS.$fileName)) {
                        echo "Moved: $fileName".LE;
                    } else {
                        echo "FILE MOVE ERROR: $fileName".LE;
                    }
			    }
				
				$moved++;
				
			} else {
				echo "File not found: $fileName".LE;

			}
		}

        if ($this->copy) {
            $msg = ($moved > 0) ? "Done, $moved files has been copied to: $this->outDir" : "No file has been copied!";
		} else {
            $msg = ($moved > 0) ? "Done, $moved files has been moved to: $this->outDir" : "No file has been moved!";
		}
		
		echo LE.$msg.LE;
	}
}
