<?php

namespace uuf6429\ElderBrother\Action;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ActionAbstract
{
    /**
     * Returns name of this action (possibly with some extra info).
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Throws an exception when action is not supported (eg: missing lib etc).
     *
     * @throws \Exception
     */
    abstract public function checkSupport();

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
     * @param int             $steps
     *
     * @return ProgressBar
     */
    protected function createProgressBar(InputInterface $input, OutputInterface $output, $steps)
    {
        if ($input->hasOption('no-progress')) {
            $output = new NullOutput();
        }

        $progress = new ProgressBar($output, $steps);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %memory:6s% %message%');
        //$progress->setRedrawFrequency(1);
        return $progress;
    }
}
