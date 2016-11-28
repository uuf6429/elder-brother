<?php

namespace uuf6429\ElderBrother\Action;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ExecuteProgram extends ActionAbstract
{
    /**
     * @var string
     */
    protected $description;

    protected $command;
    protected $breakOnFailure;
    protected $environment;
    protected $currentDir;
    protected $timeout;

    /**
     * Executes an external program.
     *
     * @param string      $description    Description of the intention of the program
     * @param string      $command        Program command line (with parameters)
     * @param bool        $breakOnFailure (Optional, default is true) Stop execution if program returns non-0 exit code
     * @param array|null  $environment    (Optional, default is null / current vars) Environment variables to pass to program
     * @param string|null $currentDir     The current directory to use for program
     * @param int         $timeout        (Optional, default is 60) The time to wait for program to finish (in seconds)
     */
    public function __construct($description, $command, $breakOnFailure = true, $environment = null, $currentDir = null, $timeout = 60)
    {
        $this->description = $description;
        $this->command = $command;
        $this->breakOnFailure = $breakOnFailure;
        $this->environment = $environment;
        $this->currentDir = $currentDir;
        $this->timeout = $timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return ($this->description ?: 'Execute External Program') . ' (ExecuteProgram)';
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported()
    {
        return true; // no special dependencies
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $process = new Process(
            $this->command,
            $this->currentDir,
            $this->environment,
            null,
            $this->timeout
        );

        if ($this->breakOnFailure) {
            $process->mustRun();
        } else {
            $process->run();
        }
    }
}
