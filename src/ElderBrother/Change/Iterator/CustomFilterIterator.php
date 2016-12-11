<?php

namespace uuf6429\ElderBrother\Change\Iterator;

use Symfony\Component\Finder\Iterator\FilterIterator;

class CustomFilterIterator extends FilterIterator
{
    /**
     * @var callable
     */
    protected $filter;

    /**
     * @param \Iterator $iterator The Iterator to filter
     * @param callable  $filter   A PHP callback
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(\Iterator $iterator, $filter)
    {
        if (!is_callable($filter)) {
            throw new \InvalidArgumentException('Invalid PHP callback.');
        }
        $this->filter = $filter;

        parent::__construct($iterator);
    }

    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        $fileInfo = $this->current();

        return call_user_func($this->filter, $fileInfo) !== false;
    }
}
