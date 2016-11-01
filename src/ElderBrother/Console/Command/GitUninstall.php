<?php

namespace uuf6429\ElderBrother\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GitUninstall extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('git-uninstall')
            ->setDescription('Uninstalls git hooks.')
            ->setHelp('This command removes all hooks to this system and restores original ones.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO
    }
}
