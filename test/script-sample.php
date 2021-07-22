<?php

use SSITU\Test\Barnett\Trooper;


require_once dirname(__DIR__,3).'/app/vendor/autoload.php'; # EDIT

$testSourceDirPath = null; # optional EDIT
$testZipDirPath = null; # optional EDIT

$tester = new Trooper($testSourceDirPath, $testZipDirPath);

# $tester->testALLtheThings();

$deletableSourceDirPath = __DIR__.'/quotes_copy';
//$tester->testChainDelete($deletableSourceDirPath); # BEWARE of set path!
$tester->testOmitResolution();
$tester->dumpResult(true);

