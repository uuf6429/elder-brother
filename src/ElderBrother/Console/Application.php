<?php

namespace uuf6429\ElderBrother\Console;

use Psr\Log;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command as SfyCommand;
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
     * @var Config\ConfigInterface
     */
    private $config;

    public function __construct()
    {
        error_reporting(-1);

        parent::__construct('Elder Brother', '1.0.0');

        $this->output = new ConsoleOutput();
        $this->logger = new ConsoleLogger($this->output);
        $this->config = new Config\Config();

        $this->logger->debug('Loading configuration...');
        foreach ([
            PROJECT_ROOT . '.brother.php',
            PROJECT_ROOT . '.brother.local.php',
        ] as $configFile) {
            if (file_exists($configFile)) {
                $this->logger->debug('Loading config file: ' . $configFile);
                $this->config->loadFromFile($configFile, $this->logger);
            } else {
                $this->logger->debug('Config file does not exist: ' . $configFile);
            }
        }

        $this->add(new Command\Run());
        $this->add(new Command\Install());
        $this->add(new Command\Uninstall());
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

    /**
     * {@inheritdoc}
     */
    public function add(SfyCommand $command)
    {
        if ($command instanceof Config\ConfigAwareInterface) {
            $command->setConfig($this->config);
        }

        if ($command instanceof Log\LoggerAwareInterface) {
            $command->setLogger($this->logger);
        }

        return parent::add($command);
    }
}
