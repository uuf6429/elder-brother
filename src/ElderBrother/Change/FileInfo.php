<?php

namespace uuf6429\ElderBrother\Change;

use PHPSQLParser\PHPSQLParser;
use Symfony\Component\Finder;

class FileInfo extends Finder\SplFileInfo
{
    /** @var array|false */
    protected $parsedSql;

    /**
     * @return array
     */
    public function getParsedSql()
    {
        if ($this->parsedSql === null) {
            if ($this->isFile()) {
                $parser = new PHPSQLParser($this->getContents());
                $this->parsedSql = $parser->parsed;
            } else {
                $this->parsedSql = false;
            }
        }

        return $this->parsedSql;
    }
}
