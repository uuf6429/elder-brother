<?php

namespace uuf6429\ElderBrother\Change;

class FileList
{
    /** @var string */
    protected $cacheKey;

    /** @var callable */
    protected $source;

    /** @var array */
    protected static $cache;

    /**
     * @param string   $cacheKey
     * @param callable $source
     */
    public function __construct($cacheKey, callable $source)
    {
        $this->cacheKey = $cacheKey;
        $this->source = $source;
    }

    /**
     * Filter by file path starting with a string.
     *
     * @param string $string
     *
     * @return static
     */
    public function startingWith($string)
    {
        $source = $this->source;

        return new self(
            $this->cacheKey . '->' . __FUNCTION__ . '(' . $string . ')',
            function () use ($source, $string) {
                return array_filter(
                    function ($file) use ($string) {
                        $fileLen = strlen($file);
                        $strnLen = strlen($string);

                        return $strnLen < $fileLen
                            && substr_compare($file, $string, 0, $strnLen) === 0;
                    },
                    $source()
                );
            }
        );
    }

    /**
     * Filter by file path ending with a string.
     *
     * @param string $string
     *
     * @return static
     */
    public function endingWith($string)
    {
        $source = $this->source;

        return new self(
            $this->cacheKey . '->' . __FUNCTION__ . '(' . $string . ')',
            function () use ($source, $string) {
                return array_filter(
                    function ($file) use ($string) {
                        $fileLen = strlen($file);
                        $strnLen = strlen($string);

                        return $fileLen > $strnLen
                            && substr_compare($file, $string, -$strnLen) === 0;
                    },
                    $source()
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
            $source = $this->source;
            self::$cache[$this->cacheKey] = $source();
        }

        return self::$cache[$this->cacheKey];
    }
}
