<?php

namespace uuf6429\ElderBrother\Action;

use uuf6429\ElderBrother\BaseProjectTest;
use uuf6429\ElderBrother\Change;

class FileListSqlTest extends BaseProjectTest
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        /** @noinspection SqlResolve */
        foreach (
            [
                'src/Acme/Combinator.php' => '<?php namespace Acme; class Combinator {}',
                'src/Acme/Comparator.php' => '<?php namespace Acme; class Comparator {}',
                'README' => 'Please read me!',
                'sql/EB-000-schema.sql' => <<<'SQL'
                    CREATE TABLE `City` (
                      `ID` int(11) NOT NULL,
                      `Name` char(35) NOT NULL DEFAULT '',
                      `CountryCode` char(3) NOT NULL DEFAULT '',
                      `District` char(20) NOT NULL DEFAULT '',
                      `Population` int(11) NOT NULL DEFAULT '0'
                    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SQL
                ,
                'sql/EB-001-data.sql' => <<<'SQL'
                    INSERT INTO `City` (`ID`, `Name`, `CountryCode`, `District`, `Population`) VALUES
                    (1, 'Kabul', 'AFG', 'Kabol', 1780000),
                    (2, 'Qandahar', 'AFG', 'Qandahar', 237500),
                    (3, 'Herat', 'AFG', 'Herat', 186800),
                    (4, 'Mazar-e-Sharif', 'AFG', 'Balkh', 127800),
                    (5, 'Amsterdam', 'NLD', 'Noord-Holland', 731200),
                    (6, 'Rotterdam', 'NLD', 'Zuid-Holland', 593321),
                    (7, 'Haag', 'NLD', 'Zuid-Holland', 440900),
                    (8, 'Utrecht', 'NLD', 'Utrecht', 234323),
                    (9, 'Eindhoven', 'NLD', 'Noord-Brabant', 201843);
SQL
                ,
                'sql/EB-002-keys-and-fix.sql' => <<<SQL
                    ALTER TABLE `City` ADD PRIMARY KEY (`ID`);
                    UPDATE `City` SET `Name` = "کندهار‎" WHERE `ID` = 2;
SQL
                ,
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
            'sql with pattern' => [
                '$expectedItems' => [
                    'sql/EB-000-schema.sql',
                    'sql/EB-002-keys-and-fix.sql',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()->filter(
                        function (Change\FileInfo $file) {
                            print_r($file->getParsedSql()); // NOTE: Instead of UPDATE we get SET... why?

                            return false;
                        }
                    )->toArray();
                },
            ],

            'sql with ddl' => [
                '$expectedItems' => [
                    'sql/EB-000-schema.sql',
                    'sql/EB-002-keys-and-fix.sql',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()->sqlWithDDL()->toArray();
                },
            ],
            'sql with ddl and dml' => [
                '$expectedItems' => [
                    'sql/EB-002-keys-and-fix.sql',
                ],
                '$itemsProvider' => function () {
                    return Change\FullChangeSet::get()->sqlWithDDL()->sqlWithDML()->toArray();
                },
            ],
        ];
    }
}
