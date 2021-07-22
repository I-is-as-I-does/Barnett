<?php
/* This file is part of Barnett | SSITU | (c) 2021 I-is-as-I-does | MIT License */
namespace SSITU\Barnett;

class Assistant
{
    public static function archiveFlag(bool $overwrite)
    {
        if (!$overwrite) {
            return \ZipArchive::CREATE;
        }
        return (\ZipArchive::CREATE | \ZipArchive::OVERWRITE);
    }

    public static function affirmDirExistence(string $dirPath)
    {
        if (!is_dir($dirPath)) {
            return @mkdir($dirPath, 0777, true);
        }
        return true;
    }

    public static function mayRemoveDotZip(string &$filename)
    {
        if (substr($filename, -4) == '.zip') {
            $filename = substr($filename, 0, -4);
        }
    }

    public static function mayAppendDotZip(string &$filename)
    {
        if (substr($filename, -4) != '.zip') {
            $filename .= '.zip';
        }
    }

    public static function findUniqueName(string &$zipName)
    {
        $zipName .= '-';
        $count = 1;
        while (file_exists($zipName . $count . '.zip')) {
            $count++;
        }
        $zipName .= $count;
    }

    public static function fileIsNow(string &$zipName)
    {
        $zipName .= date("_Y-m-d_H-i-s");
    }

    public static function reSlash(string &$dirPath, bool $finalSlash = false)
    {
        $dirPath = trim(preg_replace('/[\/\\\]+/', '/', $dirPath), '/');
        if ($finalSlash) {
            $dirPath .= '/';
        }
    }

    public static function isDotSegment(string $path)
    {
        return $path == '.' || $path == '..';
    }

    public static function normalizeExts(array &$extensions)
    {
        array_walk($extensions, function (&$v) {
            $v = strtolower(trim($v, '.'));
            if ($v == 'jpeg') {
                $v = 'jpg';
            }
        });
    }

    public static function extractNormalizedExt(string $path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext == 'jpeg') {
            return 'jpg';
        }
        return $ext;
    }

    public static function shred(string $path)
    {
        if (is_dir($path)) {
            if (!(new \FilesystemIterator($path))->valid()) {
                return @rmdir($path);
            }
            return false;
        }
        return @unlink($path);
    }

}
