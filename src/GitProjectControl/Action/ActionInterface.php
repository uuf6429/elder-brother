<?php

namespace uuf6429\GitProjectControl\Action;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface ActionInterface
{
    /**
     * Returns name of this action (possibly with some extra info).
     *
     * @return string
     */
    public function getName();

    /**
     * Execute action for the given parameters.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output);
}
