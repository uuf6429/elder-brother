<?php

namespace uuf6429\ElderBrother\Change;

use SqlParser\Parser as SqlParser;

class FileListSqlSupportTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAllParserStatementsAreSupported()
    {
        $sqlParserStatements = array_unique(array_filter(array_values(SqlParser::$STATEMENT_PARSERS)));
        sort($sqlParserStatements);

        $supportedStatements = call_user_func_array(
            'array_merge',
            $this->getProtectedStaticPropertyValue(FileList::class, 'statementTypes')
        );
        sort($supportedStatements);

        $this->assertEquals($sqlParserStatements, $supportedStatements);
    }

    /**
     * @param string $class
     * @param string $property
     * @return mixed
     */
    protected function getProtectedStaticPropertyValue($class, $property)
    {
        $refClass   = new \ReflectionClass($class);
        $refProperty = $refClass->getProperty($property);
        $refProperty->setAccessible(true);
        $value = $refProperty->getValue();
        $refProperty->setAccessible(false);

        return $value;
    }
}
