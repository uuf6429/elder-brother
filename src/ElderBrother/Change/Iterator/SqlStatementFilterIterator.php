<?php

namespace uuf6429\ElderBrother\Change\Iterator;

use SqlParser\Statement;
use SqlParser\Statements;
use uuf6429\ElderBrother\Change\FileInfo;

class SqlStatementFilterIterator extends CustomFilterIterator
{
    /**
     * @var bool
     */
    protected $recursive;

    /**
     * @param \Iterator $iterator  The Iterator to filter
     * @param callable  $filter    A PHP callback
     * @param bool      $recursive True to recurse into sub-statements
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(\Iterator $iterator, $filter, $recursive)
    {
        $this->recursive = $recursive;

        parent::__construct($iterator, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        $fileInfo = $this->current();

        if ($fileInfo instanceof FileInfo) {
            return $this->acceptStatements($fileInfo, $fileInfo->getSqlParser()->statements);
        }

        return false;
    }

    /**
     * @param FileInfo    $fileInfo
     * @param Statement[] $statements
     *
     * @return bool
     */
    protected function acceptStatements($fileInfo, $statements)
    {
        foreach ($statements as $statement) {
            if (call_user_func($this->filter, $statement, $fileInfo) === true) {
                return true;
            }

            if ($this->recursive) {
                if ($statement instanceof Statements\TransactionStatement) {
                    if ($this->acceptStatements($fileInfo, $statement->statements)) {
                        return true;
                    }
                } elseif ($statement instanceof Statements\SelectStatement) {
                    if ($this->acceptStatements($fileInfo, $statement->union)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
