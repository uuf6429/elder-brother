<?php

namespace uuf6429\ElderBrother\Change;

use SqlParser\Parser as SqlParser;
use SqlParser\Statement;
use SqlParser\Statements;
use Symfony\Component\Finder\Comparator as SfyComparator;
use Symfony\Component\Finder\Iterator as SfyIterator;

class FileList implements \IteratorAggregate, \Countable
{
    /** @var string */
    protected $cacheKey;

    /** @var callable */
    protected $source;

    /** @var array */
    protected static $cache;

    /** @var \Iterator */
    protected $sourceResult;

    /**
     * @param string   $cacheKey Unique key to identify this collection of files
     * @param callable $source   Callable that return an iterator or array of FileInfo
     */
    public function __construct($cacheKey, callable $source)
    {
        $this->cacheKey = $cacheKey;
        $this->source = $source;
    }

    //region File / Path Name filtering

    /**
     * Search file names (excluding path) by pattern.
     *
     * @param string $pattern Pattern to look for in file name (regexp, glob, or string)
     *
     * @return static
     */
    public function name($pattern)
    {
        return new self(
            $this->cacheKey . '->' . __FUNCTION__ . '(' . $pattern . ')',
            function () use ($pattern) {
                return new SfyIterator\FilenameFilterIterator(
                    $this->getSourceIterator(),
                    [$pattern],
                    []
                );
            }
        );
    }

    /**
     * Search file names (excluding path) by pattern.
     *
     * @param string $pattern Pattern to exclude files (regexp, glob, or string)
     *
     * @return static
     */
    public function notName($pattern)
    {
        return new self(
            $this->cacheKey . '->' . __FUNCTION__ . '(' . $pattern . ')',
            function () use ($pattern) {
                return new SfyIterator\FilenameFilterIterator(
                    $this->getSourceIterator(),
                    [],
                    [$pattern]
                );
            }
        );
    }

    /**
     * Search path names by pattern.
     *
     * @param string $pattern Pattern to look for in path (regexp, glob, or string)
     *
     * @return static
     */
    public function path($pattern)
    {
        return new self(
            $this->cacheKey . '->' . __FUNCTION__ . '(' . $pattern . ')',
            function () use ($pattern) {
                return new SfyIterator\PathFilterIterator(
                    $this->getSourceIterator(),
                    [$pattern],
                    []
                );
            }
        );
    }

    /**
     * Search path names by pattern.
     *
     * @param string $pattern Pattern to exclude paths (regexp, glob, or string)
     *
     * @return static
     */
    public function notPath($pattern)
    {
        return new self(
            $this->cacheKey . '->' . __FUNCTION__ . '(' . $pattern . ')',
            function () use ($pattern) {
                return new SfyIterator\PathFilterIterator(
                    $this->getSourceIterator(),
                    [],
                    [$pattern]
                );
            }
        );
    }

    //endregion

    //region FS Item Type Filtering

    /**
     * Filters out anything that is not a file.
     *
     * @return static
     */
    public function files()
    {
        return new self(
            $this->cacheKey . '->' . __FUNCTION__ . '()',
            function () {
                return new SfyIterator\FileTypeFilterIterator(
                    $this->getSourceIterator(),
                    SfyIterator\FileTypeFilterIterator::ONLY_FILES
                );
            }
        );
    }

    /**
     * Filters out anything that is not a directory.
     *
     * @return static
     */
    public function directories()
    {
        return new self(
            $this->cacheKey . '->' . __FUNCTION__ . '()',
            function () {
                return new SfyIterator\FileTypeFilterIterator(
                    $this->getSourceIterator(),
                    SfyIterator\FileTypeFilterIterator::ONLY_DIRECTORIES
                );
            }
        );
    }

    //endregion

    /**
     * Filters out items that do not match the specified level.
     *
     * @param string $level The depth expression (for example '< 1')
     *
     * @return static
     */
    public function depth($level)
    {
        $minDepth = 0;
        $maxDepth = PHP_INT_MAX;
        $comparator = new SfyComparator\NumberComparator($level);
        $comparatorTarget = intval($comparator->getTarget());

        switch ($comparator->getOperator()) {
            case '>':
                $minDepth = $comparatorTarget + 1;
                break;

            case '>=':
                $minDepth = $comparatorTarget;
                break;

            case '<':
                $maxDepth = $comparatorTarget - 1;
                break;

            case '<=':
                $maxDepth = $comparatorTarget;
                break;

            default:
                $minDepth = $maxDepth = $comparatorTarget;
                break;
        }

        return $this->filter(
            function (FileInfo $file) use ($minDepth, $maxDepth) {
                $depth = count(explode('/', str_replace('\\', '/', $file->getRelativePathname()))) - 1;

                return $depth >= $minDepth && $depth <= $maxDepth;
            }
        );
    }

    /**
     * Filters out items whose last modified do not match expression.
     *
     * @param string $date A date range string that can be parsed by `strtotime()``
     *
     * @return static
     */
    public function date($date)
    {
        return new self(
            $this->cacheKey . '->' . __FUNCTION__ . '(' . $date . ')',
            function () use ($date) {
                return new SfyIterator\DateRangeFilterIterator(
                    $this->getSourceIterator(),
                    [new SfyComparator\DateComparator($date)]
                );
            }
        );
    }

    /**
     * Filters out files not matching a string or regexp.
     *
     * @param string $pattern A pattern (string or regexp)
     *
     * @return static
     */
    public function contains($pattern)
    {
        return new self(
            $this->cacheKey . '->' . __FUNCTION__ . '(' . $pattern . ')',
            function () use ($pattern) {
                return new SfyIterator\FilecontentFilterIterator(
                    $this->getSourceIterator(),
                    [$pattern],
                    []
                );
            }
        );
    }

    /**
     * Filters out files matching a string or regexp.
     *
     * @param string $pattern A pattern (string or regexp)
     *
     * @return static
     */
    public function notContains($pattern)
    {
        return new self(
            $this->cacheKey . '->' . __FUNCTION__ . '(' . $pattern . ')',
            function () use ($pattern) {
                return new SfyIterator\FilecontentFilterIterator(
                    $this->getSourceIterator(),
                    [],
                    [$pattern]
                );
            }
        );
    }

    /**
     * Filters using an anonymous function. Function receives a Change\FileInfo and must return false to filter it out.
     *
     * @param \Closure    $closure An anonymous function
     * @param string|null $subKey  Internal use only!
     *
     * @return static
     */
    public function filter(\Closure $closure, $subKey = null)
    {
        $subKey = $subKey ? $subKey : sprintf(
            '%s(%s)',
            __FUNCTION__,
            spl_object_hash($closure)
        );

        return new self(
            $this->cacheKey . '->' . $subKey,
            function () use ($closure) {
                return new Iterator\CustomFilterIterator($this->getSourceIterator(), $closure);
            }
        );
    }

    //region SQL filtering

    protected static $statementTypes = [
        'util' => [
            Statements\ExplainStatement::class,
            Statements\AnalyzeStatement::class,
            Statements\BackupStatement::class,
            Statements\CheckStatement::class,
            Statements\ChecksumStatement::class,
            Statements\OptimizeStatement::class,
            Statements\RepairStatement::class,
            Statements\RestoreStatement::class,
            Statements\SetStatement::class,
            Statements\ShowStatement::class,
        ],
        'ddl' => [
            Statements\AlterStatement::class,
            Statements\CreateStatement::class,
            Statements\DropStatement::class,
            Statements\RenameStatement::class,
            Statements\TruncateStatement::class,
        ],
        'dml' => [
            Statements\CallStatement::class,
            Statements\DeleteStatement::class,
            Statements\InsertStatement::class,
            Statements\ReplaceStatement::class,
            Statements\SelectStatement::class,
            Statements\UpdateStatement::class,
        ],
        'tcl' => [
            Statements\TransactionStatement::class,
        ],
    ];

    /**
     * @param \Closure    $closure   The closure to call for every parsed statement. It will receive two parameters and must return a bool:
     *                               function(\SqlParser\Statement $statement, \uuf6429\ElderBrother\Change\FileInfo $file): boolean
     * @param bool        $recursive Whether closure should be called for all statements and sub-statements or not
     * @param string|null $subKey    Internal use only!
     *
     * @return FileList
     */
    public function sqlStatementFilter(\Closure $closure, $recursive = false, $subKey = null)
    {
        $subKey = $subKey ? $subKey : sprintf(
            '%s(%s,%s)',
            __FUNCTION__,
            spl_object_hash($closure),
            var_export($recursive, true)
        );

        return new self(
            $this->cacheKey . '->' . $subKey,
            function () use ($closure, $recursive) {
                return new Iterator\SqlStatementFilterIterator(
                    $this->getSourceIterator(),
                    $closure,
                    $recursive
                );
            }
        );
    }

    /**
     * Helper function to check if sql code has any of the specified statements.
     *
     * @param string[] $statementClasses The statement classes to look for
     * @param bool     $filterIn         True to "filter in", false to "filter out"
     *
     * @return FileList
     */
    protected function sqlHasStatements($statementClasses, $filterIn)
    {
        return $this->sqlStatementFilter(
            function (Statement $statement) use ($statementClasses, $filterIn) {
                foreach ($statementClasses as $class) {
                    if ($statement instanceof $class) {
                        return $filterIn;
                    }
                }

                return !$filterIn;
            },
            true,
            __FUNCTION__ . '(' . implode(',', $statementClasses) . ')'
        );
    }

    /**
     * @param string[] $keywords
     *
     * @return string[]
     */
    protected function sqlKeywordsToClasses($keywords)
    {
        $keywords = array_map('strtoupper', $keywords);
        $statements = array_replace(array_flip($keywords), SqlParser::$STATEMENT_PARSERS);

        return array_filter(array_unique($statements));
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithDDL($keywords = null)
    {
        $classes = is_null($keywords)
            ? static::$statementTypes['ddl']
            : $this->sqlKeywordsToClasses($keywords);

        return $this->sqlHasStatements($classes, true);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithoutDDL($keywords = null)
    {
        $classes = is_null($keywords)
            ? static::$statementTypes['ddl']
            : $this->sqlKeywordsToClasses($keywords);

        return $this->sqlHasStatements($classes, false);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithDML($keywords = null)
    {
        $classes = is_null($keywords)
            ? static::$statementTypes['dml']
            : $this->sqlKeywordsToClasses($keywords);

        return $this->sqlHasStatements($classes, true);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithoutDML($keywords = null)
    {
        $classes = is_null($keywords)
            ? static::$statementTypes['dml']
            : $this->sqlKeywordsToClasses($keywords);

        return $this->sqlHasStatements($classes, false);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithTCL($keywords = null)
    {
        $classes = is_null($keywords)
            ? static::$statementTypes['tcl']
            : $this->sqlKeywordsToClasses($keywords);

        return $this->sqlHasStatements($classes, true);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithoutTCL($keywords = null)
    {
        $classes = is_null($keywords)
            ? static::$statementTypes['tcl']
            : $this->sqlKeywordsToClasses($keywords);

        return $this->sqlHasStatements($classes, false);
    }

    // TODO sqlTable()
    // TODO sqlNotTable()
    // TODO sqlFilter()

    //endregion

    //region Iterator / Interface implementations

    /**
     * Returns array of file paths.
     *
     * @return string[]
     */
    public function toArray()
    {
        if (!isset(self::$cache[$this->cacheKey])) {
            self::$cache[$this->cacheKey] = array_map(
                function (FileInfo $file) {
                    return $file->getPathname();
                },
                array_values(iterator_to_array($this->getSourceIterator()))
            );
        }

        return self::$cache[$this->cacheKey];
    }

    /**
     * @return \Iterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->toArray());
    }

    /**
     * @return \Iterator
     */
    public function getSourceIterator()
    {
        if (!$this->sourceResult) {
            $source = $this->source;
            $result = $source();

            if ($result instanceof \IteratorAggregate) {
                $this->sourceResult = $result->getIterator();
            } elseif ($result instanceof \Iterator) {
                $this->sourceResult = $result;
            } elseif ($result instanceof \Traversable || is_array($result)) {
                $iterator = new \ArrayIterator();
                foreach ($result as $file) {
                    $iterator->append(
                        $file instanceof FileInfo
                            ? $file
                            : new FileInfo($file, getcwd(), $file)
                    );
                }
                $this->sourceResult = $iterator;
            } else {
                throw new \RuntimeException(
                    sprintf(
                        'Iterator or array was expected instead of %s.',
                        is_object($result) ? get_class($result) : gettype($result)
                    )
                );
            }
        }

        return $this->sourceResult;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->toArray());
    }

    //endregion
}
