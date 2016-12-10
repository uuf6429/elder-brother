<?php

namespace uuf6429\ElderBrother\Change;

use SqlParser\Parser as SqlParser;
use Symfony\Component\Finder;

class FileInfo extends Finder\SplFileInfo
{
    /** @var SqlParser */
    protected $sqlParser;

    /**
     * @return SqlParser
     */
    public function getSqlParser()
    {
        if ($this->sqlParser === null) {
            $this->sqlParser = new SqlParser($this->isFile() ? $this->getContents() : null);
        }

        return $this->sqlParser;
    }
}
