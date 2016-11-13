<?php

namespace uuf6429\ElderBrother\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use uuf6429\ElderBrother\Vcs\Manager;

class Uninstall extends CommandAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('uninstall')
            ->setDescription('Uninstalls VCS hooks.')
            ->setHelp('This command removes all VCS hooks, restoring original ones.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new Manager($this->config->getLog());
        $manager->uninstall();
    }
}
