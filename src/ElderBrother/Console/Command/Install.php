<?php

namespace uuf6429\ElderBrother\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use uuf6429\ElderBrother\Vcs\Manager;

class Install extends CommandAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Installs VCS hooks.')
            ->setHelp('This command sets up project VCS hooks.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new Manager($this->logger);
        $manager->install();
    }
}
