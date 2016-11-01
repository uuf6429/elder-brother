<?php

namespace uuf6429\ElderBrother\Action;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use uuf6429\ElderBrother\Change\FileList;

class FileValidator implements ActionInterface
{
    /**
     * @var FileList
     */
    protected $files;

    /** @var string */
    protected $reason;

    /**
     * Will stop process if $files is not empty, for the reason specified in $reason.
     *
     * @param FileList $files
     * @param string   $reason
     */
    public function __construct(FileList $files, $reason)
    {
        $this->files = $files;
        $this->reason = $reason;
    }

    public function getName()
    {
        return 'Simple file validation (FileValidator)';
    }

    public function checkSupport()
    {
        // no special dependencies
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $this->files->toArray();

        if (count($files)) {
            $bull = PHP_EOL . '- ';
            throw new \RuntimeException(
                sprintf(
                    'The following files failed validation:%s',
                    rtrim($bull . implode($bull, $files) . PHP_EOL . $this->reason)
                )
            );
        }
    }
}
