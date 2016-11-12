<?php

namespace uuf6429\ElderBrother\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->setDescription('Runs configured actions for an event.')
            ->setHelp('This command runs all actions defined in configuration for the specified event.')
            ->addOption('event', 'e', InputOption::VALUE_REQUIRED, 'The event whose actions will be run.')
            ->addOption('no-progress', null, InputOption::VALUE_NONE, 'Disables progress bar.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->isDebug()) {
            $output->write(['Running from: <info>', PROJECT_ROOT, '</info>']);
            $output->writeln('');
        }

        if (extension_loaded('xdebug')) {
            $output->writeln(
                sprintf(
                    '<bg=yellow;fg=black;>%s</>',
                    'Xdebug is enabled; performance will likely be affected.'
                )
            );
        }

        $event = $input->getOption('event');
        $actions = $this->config->get($event);

        if (!empty($actions)) {
            // See https://github.com/symfony/symfony/pull/10356 for multiple bars
            $progress = !$input->hasOption('no-progress')
                ? new ProgressBar($output, count($actions))
                : null;
            if ($progress) {
                $progress->start();
                $output->write("\n");
            }

            foreach ($actions as $action) {
                if ($progress) {
                    $output->write("\033[1A");
                    $progress->setMessage('Running "' . $action->getName() . '".');
                    $progress->advance();
                    $output->write("\n");
                } else {
                    $output->writeln('Running <info>"' . $action->getName() . '"</info>...');
                }

                try {
                    $action->execute($input, $output);
                } catch (\Exception $ex) {
                    $this->getApplication()->renderException($ex, $output);
                    if (!($ex instanceof RecoverableException)) {
                        return 1;
                    }
                }
            }

            if ($progress) {
                $progress->finish();
            }
            $output->writeln('Done.');
        } else {
            $output->writeln(
                sprintf(
                    '<bg=yellow;fg=black;>%s</>',
                    'No actions have been set up yet!'
                )
            );
        }

        return 0;
    }
}
