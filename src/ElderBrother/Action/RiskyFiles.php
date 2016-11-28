<?php

namespace uuf6429\ElderBrother\Action;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use uuf6429\ElderBrother\Change\FileList;
use uuf6429\ElderBrother\Exception\RecoverableException;

class RiskyFiles extends ActionAbstract
{
    /**
     * @var FileList
     */
    protected $files;

    /** @var string */
    protected $reason;

    /**
     * Will show a warning if `$files` is not empty, for the reason specified in `$reason`.
     *
     * @param FileList $files
     * @param string   $reason
     */
    public function __construct(FileList $files, $reason)
    {
        $this->files = $files;
        $this->reason = $reason;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Show warning for files (RiskyFiles)';
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
        $files = $this->files->toArray();

        if (count($files)) {
            $bull = PHP_EOL . '- ';
            throw new RecoverableException(
                sprintf(
                    'The following files are a potential risk:%s',
                    rtrim($bull . implode($bull, $files) . PHP_EOL . $this->reason)
                )
            );
        }
    }
}
