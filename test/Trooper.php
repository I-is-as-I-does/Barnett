<?php

namespace SSITU\Test\Barnett;

use \SSITU\Barnett\Assistant;
use \SSITU\Barnett\Barnett;

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

    public function testChainDelete($deletableSourceDirPath)
    {

        $this->rslt['chain-delete-results'] = $this->setZipSource($deletableSourceDirPath)
            ->setZipLocation($this->zipDirPath)
            ->zip()
            ->shredZippedFiles()
            ->getShredResults();
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
        if ($html) {
            $rslt = '<pre>' . $rslt . '</pre>';
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
