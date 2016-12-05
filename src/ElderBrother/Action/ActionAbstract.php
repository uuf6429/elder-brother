<?php

namespace uuf6429\ElderBrother\Action;

use Psr\Log;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use uuf6429\ElderBrother\Config;

abstract class ActionAbstract implements Config\ConfigAwareInterface, Log\LoggerAwareInterface
{
    use Log\LoggerAwareTrait;

    /**
     * @var Config\ConfigInterface
     */
    protected $config;

    /**
     * Returns name of this action (possibly with some extra info).
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Returns false when action is not supported (eg: missing lib etc).
     *
     * @return bool
     */
    abstract public function isSupported();

    /**
     * Execute action for the given parameters.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    abstract public function execute(InputInterface $input, OutputInterface $output);

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return ProgressBar
     */
    protected function createProgressBar(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption('no-progress')) {
            $output = new NullOutput();
        }

        $progress = new ProgressBar($output);
        $progress->setMessage('');
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progress->setRedrawFrequency(1);

        return $progress;
    }

    /**
     * @param Config\ConfigInterface $config
     */
    public function setConfig(Config\ConfigInterface $config)
    {
        $this->config = $config;
    }
}
