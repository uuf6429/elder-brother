<?php

namespace uuf6429\ElderBrother\Config;

use Psr\Log\LoggerInterface;
use uuf6429\ElderBrother\Action\ActionAbstract;

interface ConfigInterface
{
    /**
     * @param string          $fileName
     * @param LoggerInterface $logger
     */
    public function loadFromFile($fileName, LoggerInterface $logger);

    /**
     * @param array           $array
     * @param LoggerInterface $logger
     */
    public function loadFromArray($array, LoggerInterface $logger);

    /**
     * @return array<string, ActionAbstract[]>
     */
    public function getAllEventActions();

    /**
     * @param string $event
     * @param bool   $supportedOnly
     *
     * @return ActionAbstract[]
     */
    public function getActionsForEvent($event, $supportedOnly = true);
}
