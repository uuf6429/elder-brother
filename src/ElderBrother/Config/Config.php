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
        $this->loadFromArray($eventActions, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromArray($array, LoggerInterface $logger)
    {
        // merge config and set up actions
        foreach ($array as $event => $prioritizedActions) {
            foreach ($prioritizedActions as $key => $action) {
                $this->setUpAction($action, $logger);
                $this->config[$event][$key] = $action;
            }
        }

        // reorder actions by index
        array_map('ksort', $this->config);
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
     * @param ActionAbstract  $action
     * @param LoggerInterface $logger
     */
    private function setUpAction(ActionAbstract $action, LoggerInterface $logger)
    {
        if ($action instanceof ConfigAwareInterface) {
            $action->setConfig($this);
        }

        if ($action instanceof LoggerAwareInterface) {
            $action->setLogger($logger);
        }
    }
}
