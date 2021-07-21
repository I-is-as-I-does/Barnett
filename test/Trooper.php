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
     
    // $this->rslt['zippedFolders'] = $this->zippedFolders;
  //   $this->rslt['zippedFiles'] = $this->zippedFiles;
        $omitThesePaths = ['Ohio-subfolder/Nested','HandlingChaos.txt',"unknown"];
        $this->normalizeOmitPaths($omitThesePaths);
        $this->rslt['normalized-omit-paths'] = $omitThesePaths;


     $folderstoDel = $this->zippedFolders;
        $filesToDel = $this->zippedFiles;
    
            usort($filesToDel, function($a, $b){
                return strlen($a) <=> strlen($b);         
             });
   

        $this->rslt['sorted-zippedFiles'] = $filesToDel;

           foreach($filesToDel as $k => $file){
               foreach($omitThesePaths as $path){
               if(!Assistant::containsSubstr($file, $path, 0)){
                  $todDelete[]=      
               }
           }
        }
        $this->rslt['to-delete'] = $filesToDel;
     

        $folderstoDel = [];
        $filesToDel = [];
        if(!empty($this->zippedFolders)){
            $folderstoDel = $this->zippedFolders;
            foreach($folderstoDel as $folder){
                $search = array_search($folder, $omitThesePaths);
                if($search === false){
                    $folderstoDel[] = $folder;
                } else {
                    unset($omitThesePaths[$search]);
                    if(empty($omitThesePaths)){
                        break;
                    }
                }
            }
        }
        if(!empty($omitThesePaths) && !empty($this->zippedFiles)){
            foreach($this->zippedFiles as $file){
                $search = array_search($file, $omitThesePaths);
                if($search === false){
                    if(in_array(basename($file),$folderstoDel)){
                        $filesToDel[] = $file;
                    }               
                } else {
                    unset($omitThesePaths[$search]);
                    if(empty($omitThesePaths)){
                        break;
                    }
                }
            }

        }

        $this->rslt['files-to-delete-list'] =$filesToDel;
        $this->rslt['folder-to-delete-list'] =$folderstoDel;

        /*
     
         $toDelete = $this->zippedFiles;
         usort($toDelete, function($a, $b){
            return strlen($a) <=> strlen($b);         
         });
         $this->rslt['zippedFiles'] = $toDelete;
 
            foreach($toDelete as $k => $file){
                foreach($omitThesePaths as $path){
                if(Assistant::containsSubstr($path, $file, 0)){
                    unset($toDelete[$k]);
                    
                }
            }
        }
  */
      
/*
        $this->rslt['files-to-delete-list'] =$filesToDel;
        array_uintersect($this->zippedFolders,$omitThesePaths, function($a,$b){
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