<?php

namespace uuf6429\ElderBrother\Action;

use uuf6429\ElderBrother\Change;

class FileListTest extends \PHPUnit_Framework_TestCase
{
    public function testAllFiles()
    {
        $source = function () {
            return [
                'src/Acme/',
                'src/Acme/Combinator.php',
                'src/Acme/Comparator.php',
                'src/Acme/config.yml',
                'test/Acme/data.dml',
                'test/Acme/AllTests.phpt',
                'README',
                'LICENSE',
            ];
        };
        $fileList = new Change\FileList('', $source);

        $this->assertCount(8, $fileList->toArray());
        $this->assertCount(2, $fileList->endingWith('.php')->toArray());
        $this->assertCount(4, $fileList->startingWith('src/')->toArray());
        $this->assertCount(6, $fileList->name('/Acme/')->toArray());
    }
}
