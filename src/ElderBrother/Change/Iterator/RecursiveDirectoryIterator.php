<?php

namespace uuf6429\ElderBrother\Change\Iterator;

use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator as SfyRecursiveDirectoryIterator;
use uuf6429\ElderBrother\Change\FileInfo;

class RecursiveDirectoryIterator extends SfyRecursiveDirectoryIterator
{
    /**
     * @return FileInfo
     */
    public function current()
    {
        $result = parent::current();

        return new FileInfo($result->getRealPath(), $result->getRelativePath(), $result->getRelativePathname());
    }
}
