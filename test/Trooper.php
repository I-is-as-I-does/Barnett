<?php
/* This file is part of Barnett | SSITU | (c) 2021 I-is-as-I-does | MIT License */

/* WORK IN PROGRESS */

//@todo next : test with targeted extensions

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

    public function testALLtheThings(bool $htmlDump = false)
    {
        $this->testZipFast();
        $this->testNormalizeOmitPaths();
        $this->testNormalizeOmitPaths(true);
        $this->testChainDelete();
        $this->testChainDelete(null, ['Ohio-subfolder']);
        $this->testChainDelete(null, ['Ohio-subfolder', 'quotes/AutomaticMove.txt']);
        $this->dumpResult($htmlDump);
    }

    public function testNormalizeOmitPaths(bool $addUnknownPath = false)
    {

        $this->setZipSource($this->sourceDirPath);
        $omitThesePaths = [
            "Ohio-subfolder\\",
            "lib/Barnett\\test\\quotes\\AutomaticMove.txt",
            "quotes/HandlingChaos.txt",
            "test/quotes/HandlingChaos.txt",
            "Ohio-subfolder/Date.txt",
        ];
        if ($addUnknownPath) {
            $omitThesePaths[] = "quotes/quotes";
        }

        $rslt['given-omit-paths'] = $omitThesePaths;
        $this->normalizeOmitPaths($omitThesePaths);
        $rslt['normalized-omit-paths'] = $omitThesePaths;
        $this->rslt('testNormalizeOmitPaths', $rslt);

    }

    public function testChainDelete(?string $deletableSourceDirPath = null, array $omitThesePaths = [], bool $mockMode = true)
    {
        if (is_null($deletableSourceDirPath)) {
            if ($mockMode) {
                $deletableSourceDirPath = __DIR__ . '/quotes';
            } else {
                $this->rslt('testChainDelete', ['Trooper-message' => 'Please set a deletable source dir path OR set mock mode to true.']);
                return;
            }
        }

        $rslt['omit-paths'] = $omitThesePaths;
        $rslt['zipped-files'] = $this->setZipSource($deletableSourceDirPath)
            ->setZipLocation($this->zipDirPath)
            ->zip()
            ->shredZippedFiles($omitThesePaths, $mockMode)
            ->getZippedFilesList();
        $rslt['results'] = $this->getShredList();
        $this->rslt('testChainDelete', $rslt);
    }

    public function testZipFast()
    {

        $zipFilename = null;
        $addDate = true;
        $overwrite = false;
        $rslt['defaults'] = $this->zipFast($this->sourceDirPath, $this->zipDirPath, $zipFilename, $addDate, $overwrite);

        $zipFilename = 'bestof';
        $addDate = false;
        $rslt['filename_no-date_no-overwrite'] = $this->zipFast($this->sourceDirPath, $this->zipDirPath, $zipFilename, $addDate, $overwrite);
        $rslt['filename_no-date_no-overwrite-bis'] = $this->zipFast($this->sourceDirPath, $this->zipDirPath, $zipFilename, $addDate, $overwrite);
        $this->rslt('testZipFast', $rslt);
    }

    public function dumpResult(bool $html = false)
    {

        $rslt = $this->getJsonRslt();
        if ($html) {
            $rslt = '<pre>' . $rslt . '</pre>';
        }
        echo $rslt;
    }

    public function getJsonRslt()
    {
        $this->rslt['logs'] = $this->getLocalLogs();
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

    protected function calcRsltK(string $label)
    {
        $it = 1;
        while (array_key_exists($label . '-' . $it, $this->rslt)) {
            $it++;
        }
        return $label . '-' . $it;
    }

    protected function rslt(string $label, mixed $data)
    {
        $rk = $this->calcRsltK($label);
        $this->rslt[$rk] = $data;
    }
}
