<?php

namespace uuf6429\ElderBrother\Console;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use uuf6429\ElderBrother\Config;

class Application extends ConsoleApplication
{
    /**
     * @var ConsoleOutput
     */
    private $output;
    /**
     * @var ConsoleLogger
     */
    private $logger;
    /**
     * @var Config
     */
    private $config;

    public function __construct()
    {
        error_reporting(-1);

        parent::__construct('Elder Brother', '1.0.0');

        $this->output = new ConsoleOutput();
        $this->logger = new ConsoleLogger($this->output);
        $this->config = new Config(
            [
                'project config' => PROJECT_ROOT . '.brother.php',
                'user config' => PROJECT_ROOT . '.brother.local.php',
            ],
            $this->logger
        );

        $this->add(new Command\Run($this->config));
        $this->add(new Command\Install($this->config));
        $this->add(new Command\Uninstall($this->config));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if ($output) {
            throw new \RuntimeException('Output cannot be set.');
        }

        return parent::run($input, $this->output);
    }
}
