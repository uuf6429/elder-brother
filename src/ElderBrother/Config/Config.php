<?php

namespace uuf6429\ElderBrother\Config;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use uuf6429\ElderBrother\Action\ActionAbstract;

class Config implements ConfigInterface
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * {@inheritdoc}
     */
    public function loadFromFile($fileName, LoggerInterface $logger)
    {
        $eventActions = include $fileName;

        $this->initActions($eventActions, $logger);

        foreach ($eventActions as $event => $prioritizedActions) {
            // merge config
            $this->config[$event] = array_merge(
                isset($this->config[$event]) ? $this->config[$event] : [],
                $prioritizedActions
            );

            // reorder actions
            ksort($this->config[$event]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllEventActions()
    {
        return (array) $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function getActionsForEvent($event, $supportedOnly = true)
    {
        $config = isset($this->config[$event])
            ? array_values($this->config[$event]) : [];

        if ($supportedOnly) {
            $config = array_filter(
                $config,
                function (ActionAbstract $action) {
                    return $action->isSupported();
                }
            );
        }

        return $config;
    }

    /**
     * @param array<string,ActionAbstract[]> $eventActions
     * @param LoggerInterface                $logger
     */
    private function initActions(&$eventActions, LoggerInterface $logger)
    {
        foreach ($eventActions as $actionList) {
            foreach ($actionList as $action) {
                if ($action instanceof ConfigAwareInterface) {
                    $action->setConfig($this);
                }
                if ($action instanceof LoggerAwareInterface) {
                    $action->setLogger($logger);
                }
            }
        }
    }
}
