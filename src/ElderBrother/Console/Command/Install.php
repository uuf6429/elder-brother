<?php

namespace uuf6429\ElderBrother\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Install extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('git-install')
            ->setDescription('Installs git hooks.')
            ->setHelp('This command set up this system for use with a git repository.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \RuntimeException('Not implemented yet.');
    }
}
