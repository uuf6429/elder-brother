<?php

namespace uuf6429\ElderBrother\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use uuf6429\ElderBrother\Config;
use uuf6429\ElderBrother\Exception\RecoverableException;

class Run extends Command
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

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Runs configured actions.')
            ->setHelp('This command runs all actions defined in configuration.')
        ;
        // TODO add argument to specify which even to trigger
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->isDebug()) {
            $output->writeln('<info>Running from:</info> ' . PROJECT_ROOT);
        }

        if (extension_loaded('xdebug')) {
            $output->writeln(
                sprintf(
                    '<bg=yellow;fg=black;>%s</>',
                    'Xdebug is enabled; performance will likely be affected.'
                )
            );
        }

        $event = ''; // TODO get from param

        $actions = $this->config->get($event);

        if (!empty($actions)) {
            // See https://github.com/symfony/symfony/pull/10356 for multiple bars
            $progress = new ProgressBar($output, count($actions));
            $progress->start();
            $output->write("\n");

            foreach ($actions as $action) {
                $output->write("\033[1A");
                $progress->setMessage('Running "' . $action->getName() . '".');
                $progress->advance();
                $output->write("\n");

                try {
                    $action->execute($input, $output);
                } catch (RecoverableException $ex) {
                    $this->getApplication()->renderException($ex, $output); // TODO customize this for recoverable exceptions
                }
            }

            $progress->finish();
            $output->writeln(['', 'FINISHED']);
        } else {
            $output->writeln('<info>No actions have been set up yet!</info>');
        }
    }
}
