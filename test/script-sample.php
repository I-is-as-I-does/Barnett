<?php
/* This file is part of Barnett | SSITU | (c) 2021 I-is-as-I-does | MIT License */

use SSITU\Test\Barnett\Trooper;

require_once dirname(__DIR__,3).'/app/vendor/autoload.php'; # EDIT

$testSourceDirPath = null; # optional EDIT
$testZipDirPath = null; # optional EDIT

$tester = new Trooper($testSourceDirPath, $testZipDirPath);
$dumpInHtml = true;
$tester->testALLtheThings($dumpInHtml);
