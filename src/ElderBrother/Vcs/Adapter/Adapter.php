<?php

namespace uuf6429\ElderBrother\Vcs\Adapter;

use Psr\Log\LoggerInterface;

abstract class Adapter
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @return bool
     */
    abstract public function isAvailable();

    /**
     * @return bool
     */
    abstract public function isInstalled();

    /**
     * @return bool
     */
    abstract public function install();

    /**
     * @return bool
     */
    abstract public function uninstall();

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
