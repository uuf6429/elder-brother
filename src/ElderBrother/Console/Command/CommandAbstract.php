<?php

namespace uuf6429\ElderBrother\Console\Command;

use Symfony\Component\Console\Command\Command;
use uuf6429\ElderBrother\Config;

abstract class CommandAbstract extends Command
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct(null);

        $this->config = $config;
    }
}
