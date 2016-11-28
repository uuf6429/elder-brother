<?php

namespace uuf6429\ElderBrother\Console\Command;

use Psr\Log;
use Symfony\Component\Console\Command\Command;
use uuf6429\ElderBrother\Config;

abstract class CommandAbstract extends Command implements Config\ConfigAwareInterface, Log\LoggerAwareInterface
{
    use Log\LoggerAwareTrait;

    /**
     * @var Config\ConfigInterface
     */
    protected $config;

    /**
     * @param Config\ConfigInterface $config
     */
    public function setConfig(Config\ConfigInterface $config)
    {
        $this->config = $config;
    }
}
