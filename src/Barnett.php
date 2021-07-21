<?php
/* This file is part of Barnett | SSITU | (c) 2021 I-is-as-I-does | MIT License */
namespace SSITU\Barnett;

use \SSITU\Blueprints;

class Barnett extends \ZipArchive implements Blueprints\FlexLogsInterface

{

    use Blueprints\FlexLogsTrait;

    protected $green;

    protected $zipSourcePath;
    protected $zipSourceName;
    protected $conditions = [];

    protected $zipLocation;
    protected $flag;

    protected $zippedFiles = [];
    protected $zippedFolders = [];
    protected $shredResults = [];

    public function zipFast(string $sourceDirPath, string $zipDirPath, ?string $zipFilename = null, bool $addDate = true, bool $overwrite = false)
    {
        return $this->setZipSource($sourceDirPath)
            ->setZipLocation($zipDirPath, $zipFilename, $addDate, $overwrite)
            ->zip()
            ->getZipLocation();
    }

    public function setZipSource(string $sourceDirPath, array $theseExtOnly = [], array $omitThesePaths = [])
    {
        if ($this->isGreen()) {
            if (!Assistant::affirmDirExistence($sourceDirPath)) {
                $this->log('error', 'unable-to-resolve-zip-source', $sourceDirPath);
                $this->resetZipSource();
                return $this->notGreen();
            }
            $this->zipSourceName = basename($sourceDirPath);
            Assistant::reSlash($sourceDirPath);
            $this->zipSourcePath = $sourceDirPath;
            $this->conditions = [];
            if (!empty($theseExtOnly)) {
                Assistant::normalizeExts($theseExtOnly);
                $this->conditions['ext'] = $theseExtOnly;
            }
            if (!empty($omitThesePaths)) {
                $this->normalizeOmitPaths($omitThesePaths);
                $this->conditions['exc'] = $omitThesePaths;
            }
        }
        return $this;
    }

    public function setZipLocation(string $zipDirPath, ?string $zipFilename = null, bool $addDate = true, bool $overwrite = false)
    {
        if ($this->isGreen()) {
            if (!Assistant::affirmDirExistence($zipDirPath)) {
                $this->log('error', 'unable-to-resolve-zip-location', $zipDirPath);
                $this->resetZipLocation();
                return $this->notGreen();
            }

            $this->resolveFilename($zipFilename);
            Assistant::reSlash($zipDirPath, true);

            $zipName = $zipDirPath . $zipFilename;
            if ($addDate) {
                Assistant::fileIsNow($zipName);
            }

            if (!$overwrite && file_exists($zipName . '.zip')) {
                Assistant::findUniqueName($zipName);
            }
            $this->zipLocation = $zipName . '.zip';
            $this->flag = Assistant::archiveFlag($overwrite);
        }

        return $this;
    }

    public function zip()
    {
        $this->zippedFolders = [];
        $this->zippedFiles = [];
        if ($this->isGreen()) {

            if (!$this->zipLocation || !$this->zipSourcePath) {
                $this->log('error', 'unable-to-zip-if-unresolved-path');
                return $this->notGreen();
            }

            if ($this->open($this->zipLocation, $this->flag) !== true) {
                $this->log('error', 'unable-to-write-zip', $this->zipLocation);
                return $this->notGreen();
            }

            $this->addDir($this->zipSourcePath, $this->zipSourceName);
            $this->close();
        }
        return $this;
    }

    protected function normalizeOmitPaths(&$omitThesePaths)
    {
        array_walk($omitThesePaths, function(&$path){
            Assistant::reSlash($path);
            if (!Assistant::containsSubstr($path, $this->zipSourcePath, 0)) {
                $path = $this->zipSourcePath . '/' . $path;
            }
        });     
    }

    public function shredZippedFiles(array $omitThesePaths = [])
    {
        $this->shredResults = [];
        if ($this->isGreen() && !empty($this->zippedFiles)) {

            if (!empty($omitThesePaths)) {
                $this->normalizeOmitPaths($omitThesePaths);
            }

            foreach ($this->zippedFiles as $shredPath) {
                if (empty($omitThesePaths) || !in_array($shredPath, $omitThesePaths)) {
                    $this->shredResults[$shredPath] = @unlink($shredPath);
                }
            }

            if (in_array(false, $this->shredResults)) {
                $this->log('error', 'shredding-failed', array_filter($this->shredResults, function ($itm) {return empty($itm);}));
                return $this->notGreen();
            }
        }
        return $this;
    }

    public function getDownloadLink(?string $aliasDirPath = null, ?string $aliasFilename = null, string $text = 'download')
    {
        if ($this->isGreen()) {
            $this->resolveAliasName($aliasFilename);
            $this->resolveAliasDir($aliasDirPath);
            return '<a href="' . $aliasDirPath . $aliasFilename . '" download="' . $aliasFilename . '">' . $text . '</a>';
        }
        return '';
    }

    public function resetZipSource()
    {
        $this->zipSourceName = null;
        $this->zipSourcePath = null;
        $this->conditions = [];
        return $this;
    }

    public function resetZipLocation()
    {
        $this->zipLocation = null;
        $this->flag = null;
        return $this;
    }

    public function resetZipLists()
    {
        $this->shredResults = [];
        $this->zippedFolders = [];
        $this->zippedFiles = [];
        return $this;
    }

    public function resetAll(bool $localLogsToo = true)
    {
        $this->green = null;
        if ($localLogsToo) {
            $this->localLogs = [];
        }
        return $this->resetZipSource()
            ->resetZipLocation()
            ->resetZipLists();
    }

    public function isGreen()
    {
        return $this->green !== false;
    }

    public function getZipLocation()
    {
        return $this->zipLocation;
    }

    public function getListOfZippedFiles()
    {
        return $this->zippedFiles;
    }

    public function getShredResults()
    {
        return $this->shredResults;
    }

    protected function addDir(string $sourcePath, string $sourceName)
    {
        $this->addEmptyDir($sourceName);
        $dir = opendir($sourcePath);
        while ($file = readdir($dir)) {
            if (Assistant::isDotSegment($file)) {
                continue;
            }

            $path = $sourcePath . '/' . $file;
            if ($this->isExcluded($path) || (!is_dir($path) && !$this->isOfValidType($file))) {
                continue;
            }

            $inPath = $sourceName . '/' . $file;

            if (is_dir($path)) {
                $this->addDir($path, $inPath);
               $this->zippedFolders[] = $path;
               // $this->zippedFiles[] = $path;
            } else {
                $this->addFile($path, $inPath);
                $this->zippedFiles[] = $path;
            }
        }
    }

    protected function isOfValidType(string $file)
    {
        return (empty($this->conditions['ext']) || in_array(Assistant::extractNormalizedExt($file), $this->conditions['ext']));
    }

    protected function isExcluded(string $path)
    {
        return (!empty($this->conditions['exc']) && in_array($path, $this->conditions['exc']));
    }

    protected function notGreen()
    {
        $this->green = false;
        return $this;
    }

    protected function resolveFilename(?string &$zipFilename)
    {
        if (is_null($zipFilename)) {
            if (!empty($this->zipSourceName)) {
                $zipFilename = $this->zipSourceName;
            } else {
                $zipFilename = 'Onement';
            }
        } else {
            Assistant::mayRemoveDotZip($zipFilename);
        }
    }

    protected function resolveAliasDir(?string &$aliasDirPath)
    {
        if (is_null($aliasDirPath)) {
            $aliasDirPath = dirname($this->zipLocation) . '/';
            $this->log('warning', 'lack-of-alias-path-may-reveal-server-file-tree', $aliasDirPath);
        } else {
            Assistant::reSlash($aliasDirPath, true);
        }
    }

    protected function resolveAliasName(?string &$aliasFilename)
    {
        if (is_null($aliasFilename)) {
            $aliasFilename = basename($this->zipLocation);
        } else {
            Assistant::mayAppendDotZip($aliasFilename);
        }
    }

}
