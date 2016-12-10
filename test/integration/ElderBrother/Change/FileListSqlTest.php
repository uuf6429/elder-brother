<?php

namespace uuf6429\ElderBrother\Change;

use SqlParser\Statements\TransactionStatement;
use uuf6429\ElderBrother\BaseProjectTest;
use SqlParser\Statements\InsertStatement;

class FileListSqlTest extends BaseProjectTest
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        /** @noinspection SqlResolve */
        /** @noinspection SqlNoDataSourceInspection */
        foreach (
            [
                'src/Acme/Combinator.php' => '<?php namespace Acme; class Combinator {}',
                'src/Acme/Comparator.php' => '<?php namespace Acme; class Comparator {}',
                'README' => 'Please read me!',
                'sql/AA-000-init.sql' => <<<'SQL'
                    SET NAMES utf8;
                    SET TIME_ZONE='+00:00';
                    SET character set 'utf8';
                    DROP DATABASE IF EXISTS world;
                    CREATE DATABASE world;
SQL
                ,
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
                'sql/EB-003-more-data.sql' => <<<'SQL'
                    INSERT INTO `City` (`ID`, `Name`, `CountryCode`, `District`, `Population`) VALUES
                    (10,'Tilburg','NLD','Noord-Brabant',193238),
                    (11,'Groningen','NLD','Groningen',172701),
                    (12,'Breda','NLD','Noord-Brabant',160398),
                    (13,'Apeldoorn','NLD','Gelderland',153491);
SQL
                ,
                'sql/EB-004-much-more-data.sql' => <<<'SQL'
                    BEGIN;
                    INSERT INTO `City` (`ID`, `Name`, `CountryCode`, `District`, `Population`) VALUES
                    (14,'Nijmegen','NLD','Gelderland',152463),
                    (15,'Enschede','NLD','Overijssel',149544),
                    (16,'Haarlem','NLD','Noord-Holland',148772),
                    (17,'Almere','NLD','Flevoland',142465),
                    (18,'Arnhem','NLD','Gelderland',138020),
                    (19,'Zaanstad','NLD','Noord-Holland',135621),
                    (20,'´s-Hertogenbosch','NLD','Noord-Brabant',129170),
                    (21,'Amersfoort','NLD','Utrecht',126270),
                    (22,'Maastricht','NLD','Limburg',122087),
                    (23,'Dordrecht','NLD','Zuid-Holland',119811),
                    (24,'Leiden','NLD','Zuid-Holland',117196),
                    (25,'Haarlemmermeer','NLD','Noord-Holland',110722),
                    (26,'Zoetermeer','NLD','Zuid-Holland',110214),
                    (27,'Emmen','NLD','Drenthe',105853),
                    (28,'Zwolle','NLD','Overijssel',105819),
                    (29,'Ede','NLD','Gelderland',101574),
                    (30,'Delft','NLD','Zuid-Holland',95268),
                    (31,'Heerlen','NLD','Limburg',95052),
                    (32,'Alkmaar','NLD','Noord-Holland',92713),
                    (33,'Willemstad','ANT','Curaçao',2345),
                    (34,'Tirana','ALB','Tirana',270000),
                    (35,'Alger','DZA','Alger',2168000);
                    COMMIT;
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
            'sql that inserts more then 5 records' => [
                '$expectedItems' => [
                    'sql/EB-001-data.sql',
                    'sql/EB-004-much-more-data.sql',
                ],
                '$itemsProvider' => function () {
                    return FullChangeSet::get()->filter(
                        function (FileInfo $file) {
                            $checkStatements = function ($checkStatements, $statements) {
                                foreach ($statements as $statement) {
                                    if ($statement instanceof TransactionStatement) {
                                        if ($checkStatements($checkStatements, $statement->statements)) {
                                            return true;
                                        }
                                    } elseif ($statement instanceof InsertStatement) {
                                        if (count($statement->values) > 5) {
                                            return true;
                                        }
                                    }
                                }

                                return false;
                            };

                            return $checkStatements($checkStatements, $file->getSqlParser()->statements);
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
                    return FullChangeSet::get()->sqlWithDDL()->toArray();
                },
            ],
            'sql with ddl and dml' => [
                '$expectedItems' => [
                    'sql/EB-002-keys-and-fix.sql',
                ],
                '$itemsProvider' => function () {
                    return FullChangeSet::get()->sqlWithDDL()->sqlWithDML()->toArray();
                },
            ],
            'sql without ddl and dml' => [
                '$expectedItems' => [
                    'sql/EB-002-keys-and-fix.sql',
                ],
                '$itemsProvider' => function () {
                    return FullChangeSet::get()->sqlWithoutDDL()->sqlWithoutDML()->toArray();
                },
            ],
            'sql with tcl' => [
                '$expectedItems' => [
                    'sql/EB-004-much-more-data.sql'
                ],
                '$itemsProvider' => function () {
                    return FullChangeSet::get()->sqlWithTCL()->toArray();
                },
            ],
        ];
    }
}
