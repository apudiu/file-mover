<?php
/**
 * Created by PhpStorm.
 * User: Apu
 * Date: 3/3/2019
 * Time: 10:43 AM
 */
require_once 'Process.php';

class MoveFileToDir
{
    private $csvFile;
    private $csvData = [];

    private $inDirPath;

    private $outDir;
    private $outDirName = 'Indexed_documents';
    private $outDirPath;


    public function __construct(string $csvFile, string $inDirPath, $outDir=false)
    {
        $this->csvFile = $csvFile;

        if (!is_dir($inDirPath)) {
            die("{$inDirPath} is not a valid directory.");
        }

        $this->inDirPath = $inDirPath;

        if ($outDir) {
            $this->outDir = $outDir;
        } else {
            $this->outDir = $inDirPath;
        }

        $this->outDirPath = $this->outDir.DIRECTORY_SEPARATOR.$this->outDirName;

        if (file_exists($this->outDirPath)) {
            die('Cannot continue, output directory already exits: '. $this->outDirPath);
        }


        // capturing CSV data
        $this->captureCSVData();

        // moving files
        $this->moveFiles();
    }

    /**
     * Gets necessary fields from CSV
     */
    private function captureCSVData() {

        // Opening CSV for reading
        $handle = fopen($this->csvFile, 'r');

        $count = 0;

        // looping each line
        while (($line = fgetcsv($handle))) {

            // skipping first entry/CSV title
            if ($count == 0) {
                $count++;
                continue;
            }

            list(
                $no, $done, $kyc, $nid, $acknowledgement,
                $pack, $lot, $job, $delivery_status, $iqc_status,
                $primary_reject_reason, $secondary_reject_reason, $remarks
            ) = $line;

            // taking only necessary fields
            if (!empty($lot)) {
                $this->csvData[$no] = [$lot, $pack];
            }

            echo '=> '.$count.PHP_EOL;

            $count++;
        }

        fclose($handle);
    }

    /**
     * Moves files
     */
    private function moveFiles() {

        // move count
        $moved = 1;

        // surce directory content list
        $inDirContentList = $this->directoryScan($this->inDirPath);

        // creating output dir
        if ($this->createDirectory($this->outDirPath)) {

            // checking each file in CSV
            foreach ($this->csvData as $file => $value) {

                list($lot, $pack) = $value;

                // creating lot dir
                if (($lotDir = $this->createDirectory($this->outDirPath.DIRECTORY_SEPARATOR.'lot-'.$lot, true))) {

                    // creating pack dir
                    if (($packDir = $this->createDirectory($lotDir.DIRECTORY_SEPARATOR.'pack-'.$pack, true))) {

                        // taking matched files from (input)directory
                        $matchedFiles = preg_grep("/$file/", $inDirContentList);

                        // if match found
                        if (count($matchedFiles)) {

                            // copying every file
                            foreach ($matchedFiles as $fileName => $location) {

                                if (rename($location, $packDir.DS.$fileName)) {

                                    $moved++;
                                    echo ".";

                                } else {

                                    echo "FILE MOVE ERROR: $fileName".LE;
                                }
                            }
                        }
                    }
                }
            }

            $msg = ($moved > 0) ? "Done, $moved file(s) has been moved to: $this->outDirPath" : "No file has been moved!";
            echo LE.$msg.LE;
        }
    }

    /**
     * Created directory
     * @param string $path  full path of the directory
     * @param bool $check checks if directory exists
     * @return bool
     */
    private function createDirectory($path, $check=false) {

        if ($check) {

            if (!file_exists($path)) {
               $d = mkdir($path, '0777', true);
               $d = ($d) ? $path : false;
            } else {
                $d = $path;
            }

        } else {
            $d = mkdir($path, '0777', true);
            $d = ($d) ? $path : false;
        }
        return $d;
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

                // filename as key & location as value
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
}
