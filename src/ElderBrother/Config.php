<?php

namespace uuf6429\ElderBrother;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use uuf6429\ElderBrother\Action\ActionAbstract;

class Config
{
    /**
     * @var string[]
     */
    protected $paths;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param string[]        $paths
     * @param LoggerInterface $logger
     */
    public function __construct($paths, LoggerInterface $logger = null)
    {
        $this->paths = $paths;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @return self
     */
    protected function load()
    {
        $this->config = [];

        foreach ($this->paths as $path) {
            if (file_exists($path)) {
                $this->logger->debug('Loading config file: ' . $path);

                $config = include $path;

                foreach ($config as $event => $prioritizedActions) {
                    // merge config
                    $this->config[$event] = array_merge(
                        isset($this->config[$event]) ? $this->config[$event] : [],
                        $prioritizedActions
                    );

                    // reorder actions
                    ksort($this->config[$event]);
                }
            } else {
                $this->logger->debug('Config file does not exist: ' . $path);
            }
        }

        $this->logger->debug('Configuration loaded.');

        return $this;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        if (is_null($this->config)) {
            $this->load();
        }

        return $this->config;
    }

    /**
     * @param string $event
     * @param bool   $supportedOnly
     *
     * @return ActionAbstract[]
     */
    public function get($event, $supportedOnly = true)
    {
        if (is_null($this->config)) {
            $this->load();
        }

        $config = isset($this->config[$event])
            ? array_values($this->config[$event]) : [];

        if ($supportedOnly) {
            $config = array_filter(
                $config,
                function (ActionAbstract $action) {
                    try {
                        $action->checkSupport();

                        return true;
                    } catch (\Exception $ex) {
                        $this->logger->warning(
                            sprintf(
                                '%s is not supported: %s.',
                                $action->getName(),
                                $ex->getMessage()
                            )
                        );
                    }
                }
            );
        }

        return $config;
    }
}
