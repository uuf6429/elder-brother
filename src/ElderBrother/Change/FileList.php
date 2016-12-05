<?php

namespace uuf6429\ElderBrother\Change;

use Symfony\Component\Finder\Comparator;
use Symfony\Component\Finder\Iterator;

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
                return new Iterator\FilenameFilterIterator(
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
                return new Iterator\FilenameFilterIterator(
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
                return new Iterator\PathFilterIterator(
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
                return new Iterator\PathFilterIterator(
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
                return new Iterator\FileTypeFilterIterator(
                    $this->getSourceIterator(),
                    Iterator\FileTypeFilterIterator::ONLY_FILES
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
                return new Iterator\FileTypeFilterIterator(
                    $this->getSourceIterator(),
                    Iterator\FileTypeFilterIterator::ONLY_DIRECTORIES
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
        $comparator = new Comparator\NumberComparator($level);
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
                return new Iterator\DateRangeFilterIterator(
                    $this->getSourceIterator(),
                    [new Comparator\DateComparator($date)]
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
                return new Iterator\FilecontentFilterIterator(
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
                return new Iterator\FilecontentFilterIterator(
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
     * @param \Closure $closure An anonymous function
     *
     * @return static
     */
    public function filter(\Closure $closure)
    {
        return $this->filterByClosure(
            __FUNCTION__ . '(' . spl_object_hash($closure) . ')',
            $closure
        );
    }

    /**
     * Helper method that can be reused in more specific methods.
     *
     * @param string   $subKey  The cache sub key to use
     * @param \Closure $closure The filtering callback
     *
     * @return FileList
     */
    protected function filterByClosure($subKey, \Closure $closure)
    {
        return new self(
            $this->cacheKey . '->' . $subKey,
            function () use ($closure) {
                return new Iterator\CustomFilterIterator(
                    $this->getSourceIterator(),
                    [$closure]
                );
            }
        );
    }

    //region SQL filtering

    protected static $sqlDCLKeywords = [
        'GRANT',
        'REVOKE',
    ];

    protected static $sqlDDLKeywords = [
        'CREATE',
        'ALTER',
        'DROP',
        'TRUNCATE',
        'COMMENT',
        'RENAME',
    ];

    protected static $sqlDMLKeywords = [
        'INSERT',
        'UPDATE',
        'DELETE',
        'MERGE',
        'CALL',
        'EXPLAIN PLAN',
        'LOCK TABLE',
    ];

    protected static $sqlDQLKeywords = [
        'SELECT',
        'SHOW',
    ];

    protected static $sqlTCLKeywords = [
        'COMMIT',
        'ROLLBACK',
        'SAVEPOINT',
        'SET TRANSACTION',
    ];

    /**
     * Helper function to check type of sql statements.
     *
     * @param string[] $keywords The keywords to look for
     * @param bool     $filterIn True to "filter in", false to "filter out"
     *
     * @return FileList
     */
    protected function sqlKeywords($keywords, $filterIn)
    {
        return $this->filterByClosure(
            __FUNCTION__ . '(' . is_null($keywords) ? '*' : implode(',', $keywords) . ')',
            function (FileInfo $file) use ($keywords, $filterIn) {
                if (!($sql = $file->getParsedSql())) {
                    return !$filterIn;
                }

                // TODO Investigate if this code breaks for sub-statements.
                foreach ($keywords as $keyword) {
                    if (array_key_exists($keyword, $sql)) {
                        return $filterIn;
                    }
                }

                return !$filterIn;
            }
        );
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithDCL($keywords = null)
    {
        $keywords = is_null($keywords) ? static::$sqlDCLKeywords : array_map('strtoupper', $keywords);

        return $this->sqlKeywords($keywords, true);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithoutDCL($keywords = null)
    {
        $keywords = is_null($keywords) ? static::$sqlDCLKeywords : array_map('strtoupper', $keywords);

        return $this->sqlKeywords($keywords, false);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithDDL($keywords = null)
    {
        $keywords = is_null($keywords) ? static::$sqlDDLKeywords : array_map('strtoupper', $keywords);

        return $this->sqlKeywords($keywords, true);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithoutDDL($keywords = null)
    {
        $keywords = is_null($keywords) ? static::$sqlDDLKeywords : array_map('strtoupper', $keywords);

        return $this->sqlKeywords($keywords, false);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithDML($keywords = null)
    {
        $keywords = is_null($keywords) ? static::$sqlDMLKeywords : array_map('strtoupper', $keywords);

        return $this->sqlKeywords($keywords, true);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithoutDML($keywords = null)
    {
        $keywords = is_null($keywords) ? static::$sqlDMLKeywords : array_map('strtoupper', $keywords);

        return $this->sqlKeywords($keywords, false);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithDQL($keywords = null)
    {
        $keywords = is_null($keywords) ? static::$sqlDQLKeywords : array_map('strtoupper', $keywords);

        return $this->sqlKeywords($keywords, true);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithoutDQL($keywords = null)
    {
        $keywords = is_null($keywords) ? static::$sqlDQLKeywords : array_map('strtoupper', $keywords);

        return $this->sqlKeywords($keywords, false);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithTCL($keywords = null)
    {
        $keywords = is_null($keywords) ? static::$sqlTCLKeywords : array_map('strtoupper', $keywords);

        return $this->sqlKeywords($keywords, true);
    }

    /**
     * @param string[]|null $keywords
     *
     * @return FileList
     */
    public function sqlWithoutTCL($keywords = null)
    {
        $keywords = is_null($keywords) ? static::$sqlTCLKeywords : array_map('strtoupper', $keywords);

        return $this->sqlKeywords($keywords, false);
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
