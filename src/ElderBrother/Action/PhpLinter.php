<?php

namespace uuf6429\ElderBrother\Action;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use uuf6429\ElderBrother\Change\FileList;

class PhpLinter extends ActionAbstract
{
    /** @var FileList */
    protected $files;

    /**
     * Ensures that all the provided files are valid PHP files, terminating the
     * process with an error and non-zero exit code, if not.
     *
     * @param FileList $files The files to check
     */
    public function __construct(FileList $files)
    {
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'PHP Syntax Check (PhpLinter)';
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported()
    {
        $process = new Process('php -v');

        if ($process->run() !== 0) {
            $this->logger->warning(
                sprintf(
                    'PHP could not be executed successfully (exit code: %d): %s',
                    $process->getExitCode(),
                    $process->getOutput()
                )
            );
        }

        return $process->isSuccessful();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $this->files->toArray();

        $progress = $this->createProgressBar($input, $output, count($files));
        $progress->start();

        $failed = [];

        foreach ($files as $file) {
            $progress->setMessage('Processing ' . $file . '...');
            $process = new Process('php -l ' . escapeshellarg($file));

            if ($process->run() !== 0) {
                $failed[$file] = array_filter(
                    explode("\n", str_replace(PHP_EOL, "\n", $process->getErrorOutput() ?: $process->getOutput())),
                    function ($line) {
                        return strlen($line)
                            && substr($line, 0, 15) !== 'Errors parsing ';
                    }
                );
            }

            $progress->advance();
        }

        if (count($failed)) {
            $message = 'PhpLinter failed for the following file(s):';
            foreach ($failed as $file => $result) {
                $message .= PHP_EOL . '- ' . $file . ':';
                $message .= PHP_EOL . ' - ' . implode(PHP_EOL . ' - ', $result);
            }
            throw new \RuntimeException($message);
        }

        $progress->finish();
    }
}
