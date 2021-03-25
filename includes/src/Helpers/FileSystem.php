<?php

namespace JTL\Helpers;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class FileSystem
 * @package JTL\Helpers
 * @since 5.0.0
 */
class FileSystem
{
    /**
     * @param string $dir
     * @return bool
     */
    public static function delDirRecursively(string $dir): bool
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        $res      = true;
        foreach ($iterator as $fileInfo) {
            $fileName = $fileInfo->getFilename();
            if ($fileName !== '.gitignore' && $fileName !== '.gitkeep') {
                $func = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
                $res  = $res && $func($fileInfo->getRealPath());
            }
        }

        return $res;
    }
}
