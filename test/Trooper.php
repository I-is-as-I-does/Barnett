<?php

namespace SSITU\Barnett\Test;

use \SSITU\Barnett\Barnett;
use \SSITU\Barnett\Assistant;

class Trooper extends Barnett
{

    protected $sourceDirPath;
    protected $zipDirPath;
    protected $rslt = [];

    public function __construct(?string $sourceDirPath = null, ?string $zipDirPath = null)
    {
        if (empty($sourceDirPath)) {
            $sourceDirPath = __DIR__ . '/quotes';
        }
        if (empty($zipDirPath)) {
            $zipDirPath = __DIR__ . '/zipDest';
        }
        new parent();
        $this->sourceDirPath = $sourceDirPath;
        $this->zipDirPath = $zipDirPath;
       
    }

    public function testALLtheThings()
    {
        $this->testZipFast();
    }

    public function testChainDelete($deletableSourceDirPath){

       $this->rslt['chain-delete-results'] = $this->setZipSource($deletableSourceDirPath)
                   ->setZipLocation($this->zipDirPath)
                   ->zip()
                   ->shredZippedFiles()
                   ->getShredResults();
    }

    public function testOmitResolution()
    {
        /*
        $this->zipSourcePath = 'sourceDirPath';
        $this->zippedFiles = ['sourceDirPath/file1.ext', 'sourceDirPath/file2.ext', 'sourceDirPath/file3.ext','sourceDirPath/subfolder/subfile.ext', 'sourceDirPath/subfolder/another/last.ext'];
        $this->zippedFolders = ['sourceDirPath/subfolder', 'sourceDirPath/subfolder/another'];

        */

        $this->setZipSource($this->sourceDirPath)->setZipLocation($this->zipDirPath,null,true,false)->zip();
        $this->rslt['zippedFiles'] = $this->zippedFiles;
        $this->rslt['zippedFolders'] = $this->zippedFolders;

        $omitThesePaths = ['Ohio-subfolder/','HandlingChaos.txt'];
        $this->normalizeOmitPaths($omitThesePaths);
        $this->rslt['normalized-omit-paths'] = $omitThesePaths;


        $folders = $this->zippedFolders;
        $files = $this->zippedFiles;
        foreach($omitThesePaths as $path){
            if(is_dir($path)){
                
            }
            $dkey = array_search($path, $folders);
            if($dkey !== false){
                unset($folders[$dkey]);
                array_walk($files, function(&$itm) use($path){
                    if (Assistant::containsSubstr($itm, $path, 0)) {
                        unset($itm);
                    }
                });
            } else {
                $fkey = array_search($path, $files);
                if($fkey !== false){
                    unset($files[$fkey]);
                }
            }

        }

    
        $this->rslt['reduced-file-list-to-shred'] =$files;
        $this->rslt['reduced-folder-list-to-shred'] =$folders;
        
        /*array_uintersect($this->zippedFolders,$omitThesePaths, function($a,$b){
            if ($a === $b) {
                return -1;
            }
            if (Assistant::containsSubstr($a, $b, 0)) {
                $this->rslt['contains'][]= ['a'=>$a, 'b'=>$b];
                return 0;
            }
            return 1;
        });*/
      $this->resetAll();
    }

    public function testZipFast()
    {

        $zipFilename = null;
        $addDate = true;
        $overwrite = false;
        $this->rslt['zipfast_defaults'] = $this->zipFast($this->sourceDirPath, $this->zipDirPath, $zipFilename, $addDate, $overwrite);

        $zipFilename = 'bestof';
        $addDate = false;
        $this->rslt['zipfast_filename_no-date_no-overwrite'] = $this->zipFast($this->sourceDirPath, $this->zipDirPath, $zipFilename, $addDate, $overwrite);
        $this->rslt['zipfast_filename_no-date_no-overwrite-bis'] = $this->zipFast($this->sourceDirPath, $this->zipDirPath, $zipFilename, $addDate, $overwrite);
    }

    public function dumpResult($html = false)
    {

        $rslt = $this->getJsonRslt();
        if($html){
            $rslt = '<pre>'.$rslt.'</pre>';
        }
        echo $rslt;
    }

    public function getJsonRslt()
    {
        return json_encode($this->rslt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);

    }

    public function saveRslt(?string $path)
    {
        if (!empty($this->rslt)) {
            if (is_null($path)) {
                $path = __DIR__ . '/testResults.json';
            }
            file_put_contents($path, $this->getJsonRslt(), LOCK_EX);
        }
    }

}