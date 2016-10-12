<?php

namespace uuf6429\GitProjectControl\Action;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use uuf6429\GitProjectControl\Change\FileList;

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
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $this->files->toArray();
        $progress = new ProgressBar($output, count($files));
        $progress->start();

        foreach ($files as $file) {
            $progress->setMessage('Processing ' . $file . '...');

            $outp = null;
            $exit = null;
            exec('php -l ' . escapeshellarg($file), $outp, $exit);

            if ($exit) {
                throw new \RuntimeException(
                    sprintf(
                        'PhpLinter failed for %s:\n- %s\nExit Code: %s',
                        $file,
                        implode('\n- ', $outp),
                        $exit
                    )
                );
            }

            $progress->advance();
        }

        $progress->finish();
    }
}
