<?php

namespace uuf6429\ElderBrother\Action;

use uuf6429\ElderBrother\BaseProjectTest;
use uuf6429\ElderBrother\Change;

class FileListTest extends BaseProjectTest
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        foreach (
            [
                'src/Acme/Combinator.php' => '<?php namespace Acme; class Combinator {}',
                'src/Acme/Comparator.php' => '<?php namespace Acme; class Comparator {}',
                'src/Acme/config.yml' => '',
                'test/Acme/data.dml' => '',
                'test/Acme/AcmeTest.php' => '<?php namespace Acme; class AcmeTest extends \PHPUnit_Framework_TestCase {}',
                'README' => 'Please read me!',
                'LICENSE' => 'Copyright 1986',
                'CONTRIBUTE' => 'Meh.',
            ] as $filename => $content
        ) {
            if (!is_dir(dirname($filename))) {
                mkdir(dirname($filename), 0755, true);
            }
            file_put_contents($filename, $content);
        }
    }

    /**
     * @param string[] $expectedItems
     * @param \Closure $itemsProvider
     *
     * @dataProvider fileListQueryDataProvider
     */
    public function testFileListQuery($expectedItems, $itemsProvider)
    {
        $baseDir = getcwd();
        $actualItems = array_map(
            function ($file) use ($baseDir) {
                return str_replace([$baseDir . DIRECTORY_SEPARATOR, '\\'], ['', '/'], $file);
            },
            $itemsProvider()
        );
        sort($actualItems);

        $this->assertEquals($expectedItems, $actualItems);
        $this->assertSame($expectedItems, $actualItems);
    }

    /**
     * @return array
     */
    public function fileListQueryDataProvider()
    {
        return [
            // tests for filesystem item type
            'files' => [
                '$expectedItems' => [
                    'CONTRIBUTE',
                    'LICENSE',
                    'README',
                    'src/Acme/Combinator.php',
                    'src/Acme/Comparator.php',
                    'src/Acme/config.yml',
                    'test/Acme/AcmeTest.php',
                    'test/Acme/data.dml',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()->files()->toArray();
                },
            ],
            'directories' => [
                '$expectedItems' => [
                    'src',
                    'src/Acme',
                    'test',
                    'test/Acme',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()->directories()->toArray();
                },
            ],

            // tests for file name / path patterns
            'php files' => [
                '$expectedItems' => [
                    'src/Acme/Combinator.php',
                    'src/Acme/Comparator.php',
                    'test/Acme/AcmeTest.php',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()->name('*.php')->toArray();
                },
            ],
            'all items in src (3 files + 1 dir)' => [
                '$expectedItems' => [
                    'src/Acme',
                    'src/Acme/Combinator.php',
                    'src/Acme/Comparator.php',
                    'src/Acme/config.yml',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()->path('src/')->toArray();
                },
            ],
            'all items with Acme in their pathname' => [
                '$expectedItems' => [
                    'src/Acme',
                    'test/Acme',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()->name('Acme')->toArray();
                },
            ],
            'all php files not in src/' => [
                '$expectedItems' => [
                    'test/Acme/AcmeTest.php',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()->notPath('src/')->name('*.php')->toArray();
                },
            ],

            // tests for file contents
            'files containing "class"' => [
                '$expectedItems' => [
                    'src/Acme/Combinator.php',
                    'src/Acme/Comparator.php',
                    'test/Acme/AcmeTest.php',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()->contains('class')->toArray();
                },
            ],
            'base classes' => [
                '$expectedItems' => [
                    'src/Acme/Combinator.php',
                    'src/Acme/Comparator.php',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()
                        ->name('*.php')
                        ->notContains('/extends\\s+([\\w_\\\\]+)\\s*\\{/')
                        ->toArray();
                },
            ],
            'phpunit test classes' => [
                '$expectedItems' => [
                    'test/Acme/AcmeTest.php',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()
                        ->name('*.php')
                        ->contains('/class\\s+([\\w_]+)\\s+extends\\s\\\\?PHPUnit_Framework_TestCase\\s*{/')
                        ->toArray();
                },
            ],
            'files without ext' => [
                '$expectedItems' => [
                    'CONTRIBUTE',
                    'LICENSE',
                    'README',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()
                        ->files()
                        ->notName('*.*')
                        ->toArray();
                },
            ],

            // tests for custom filter
            'filter for some files' => [
                '$expectedItems' => [
                    'CONTRIBUTE',
                    'LICENSE',
                    'README',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()
                        ->filter(
                            function (Change\FileInfo $file) {
                                return in_array($file->getFilename(), ['README', 'LICENSE', 'CONTRIBUTE']);
                            }
                        )
                        ->toArray();
                },
            ],

            // tests for path depth
            'top level files' => [
                '$expectedItems' => [
                    'CONTRIBUTE',
                    'LICENSE',
                    'README',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()->files()->depth('< 2')->toArray();
                },
            ],
            'top level directories' => [
                '$expectedItems' => [
                    'src',
                    'src/Acme',
                    'test',
                    'test/Acme',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()->directories()->depth('< 2')->toArray();
                },
            ],
            'deep items' => [
                '$expectedItems' => [
                    'src/Acme/Combinator.php',
                    'src/Acme/Comparator.php',
                    'src/Acme/config.yml',
                    'test/Acme/AcmeTest.php',
                    'test/Acme/data.dml',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()->depth('> 1')->toArray();
                },
            ],
        ];
    }
}
