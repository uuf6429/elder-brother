<?php

namespace uuf6429\ElderBrother\Vcs;

use Psr\Log\LoggerInterface;

class Manager
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Adapter\Adapter[]
     */
    protected $adapters;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->adapters = [
            new Adapter\Git($logger),
        ];
    }

    public function install()
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->isAvailable() && !$adapter->isInstalled()) {
                $adapter->install();
            }
        }
    }

    public function uninstall()
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->isAvailable() && $adapter->isInstalled()) {
                $adapter->uninstall();
            }
        }
    }
}
