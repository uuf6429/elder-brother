<?php

namespace uuf6429\GitProjectControl\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GitInstall extends Command
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
        // TODO add arguments for specifying which hooks to install
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO
    }
}
