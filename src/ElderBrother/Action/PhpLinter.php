<?php

namespace uuf6429\ElderBrother\Action;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use uuf6429\ElderBrother\Change\FileList;

class PhpLinter implements ActionInterface
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
    public function checkSupport()
    {
        $output = $exitCode = null;
        exec('php -v', $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException(
                sprintf(
                    'PHP could not be executed successfully (exit code: %d): %s',
                    $exitCode,
                    (count($output) > 1 ? PHP_EOL : '') . implode(PHP_EOL, $output)
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $this->files->toArray();

        $progress = new ProgressBar($output, count($files));
        $progress->start();

        $failed = [];

        foreach ($files as $file) {
            $progress->setMessage('Processing ' . $file . '...');

            $outp = null;
            $exit = null;
            exec('php -l ' . escapeshellarg($file), $outp, $exit);

            if ($exit) {
                $failed[$file] = array_filter(
                    $outp,
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
            foreach ($failed as $file => $output) {
                $message .= PHP_EOL . '- <options=underline>' . $file . '</>:';
                $message .= PHP_EOL . ' - ' . implode(PHP_EOL . ' - ', $output);
            }
            throw new \RuntimeException($message);
        }

        $progress->finish();
    }
}
