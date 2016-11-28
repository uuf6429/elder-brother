<?php

namespace uuf6429\ElderBrother\Action;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCode extends ActionAbstract
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @param string   $description Description of what this code does
     * @param callable $callback    The callback to execute. The callback will receive $config, $input and $output as parameters
     */
    public function __construct($description, $callback)
    {
        $this->description = $description;
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return ($this->description ?: 'Execute Custom Code') . ' (ExecuteCode)';
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported()
    {
        return true; // no special dependencies
    }

    /**
     * Execute action for the given parameters.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        call_user_func($this->callback, $this->config, $input, $output);
    }
}
