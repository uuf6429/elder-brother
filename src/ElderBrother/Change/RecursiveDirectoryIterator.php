<?php

namespace uuf6429\ElderBrother\Change;

use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator as SfyRecursiveDirectoryIterator;

class RecursiveDirectoryIterator extends SfyRecursiveDirectoryIterator
{
    /**
     * Return an instance of SplFileInfo with support for relative paths.
     *
     * @return FileInfo
     */
    public function current()
    {
        $result = parent::current();

        return new FileInfo($result->getRealPath(), $result->getRelativePath(), $result->getRelativePathname());
    }
}
