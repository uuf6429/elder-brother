<?php

namespace uuf6429\ElderBrother\Change;

use Symfony\Component\Finder\Comparator;
use Symfony\Component\Finder\Iterator;
use Symfony\Component\Finder\SplFileInfo as SfyFileInfo;

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
     * @param callable $source   Callable that return an iterator or array of SplFileInfo
     */
    public function __construct($cacheKey, callable $source)
    {
        $this->cacheKey = $cacheKey;
        $this->source = $source;
    }

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
            function (SfyFileInfo $file) use ($minDepth, $maxDepth) {
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
     * Filters using an anonymous function. Function receives a \SplFileInfo and must return false to filter it out.
     *
     * @param \Closure $closure An anonymous function
     *
     * @return static
     */
    public function filter(\Closure $closure)
    {
        return new self(
            $this->cacheKey . '->' . __FUNCTION__ . '(' . spl_object_hash($closure) . ')',
            function () use ($closure) {
                return new Iterator\CustomFilterIterator(
                    $this->getSourceIterator(),
                    [$closure]
                );
            }
        );
    }

    /**
     * Returns array of file paths.
     *
     * @return string[]
     */
    public function toArray()
    {
        if (!isset(self::$cache[$this->cacheKey])) {
            self::$cache[$this->cacheKey] = array_map(
                function (\SplFileInfo $file) {
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
    protected function getSourceIterator()
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
                        $file instanceof \SplFileInfo
                            ? $file
                            : new SfyFileInfo($file, getcwd(), $file)
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
}
