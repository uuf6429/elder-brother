<?php

namespace uuf6429\ElderBrother\Action;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo as SfyFileInfo;
use Symfony\Component\Process\Process;
use uuf6429\ElderBrother\Change\FileList;

class PhpCsFixer extends ActionAbstract
{
    const NONE_LEVEL = 0;
    const PSR0_LEVEL = 1;
    const PSR1_LEVEL = 3;
    const PSR2_LEVEL = 7;
    const SYMFONY_LEVEL = 15;
    const CONTRIB_LEVEL = 32;

    /**
     * @var FileList
     */
    protected $files;

    /**
     * @var int|null
     */
    protected $level;

    /**
     * @var string[]
     */
    protected $fixers;

    /**
     * @var bool
     */
    protected $addAutomatically;

    /**
     * Runs all the provided files through PHP-CS-Fixer, fixing any code style issues.
     *
     * @param FileList $files            The files to check
     * @param int|null $level            (Optional, defaults to NONE_LEVEL) Fixer level to use
     * @param string[] $fixers           (Optional, default is empty) Set the fixers to use
     * @param bool     $addAutomatically (Optional, default is true) Whether to add modified files to commit or not
     */
    public function __construct(FileList $files, $level = self::NONE_LEVEL, $fixers = [], $addAutomatically = true)
    {
        $this->files = $files;
        $this->level = $level;
        $this->fixers = $fixers;
        $this->addAutomatically = $addAutomatically;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'PHP Code Style Fixer (PhpCsFixer)';
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported()
    {
        if (!class_exists('\Symfony\CS\Fixer')) {
            $this->logger->warning(
                'PHP-CS-Fixer could not be loaded: class \Symfony\CS\Fixer not found.'
            );

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $fixer = $this->getCsFixer();
        $fixers = $this->resolveFixers($fixer, $this->level, $this->fixers);
        $cache = new \Symfony\CS\FileCacheManager(false, getcwd(), $fixers);

        if(!$this->files->count()){
            return;
        }

        $progress = $this->createProgressBar($input, $output);
        $progress->start($this->files->count());

        /** @var SfyFileInfo $file */
        foreach ($this->files->getSourceIterator() as $file) {
            $progress->setMessage('Checking <info>' . $file->getRelativePathname() . '</info>...');

            if ($fixer->fixFile($file, $fixers, false, false, $cache) && $this->addAutomatically) {
                $process = new Process('git add ' . escapeshellarg($file->getRelativePathname()));
                $process->mustRun();
            }

            $progress->advance();
        }

        $progress->setMessage('Finished.');
        $progress->finish();
    }

    /**
     * @return \Symfony\CS\Fixer
     */
    private function getCsFixer()
    {
        $fixer = new \Symfony\CS\Fixer();
        $fixer->registerBuiltInFixers();
        $fixer->registerBuiltInConfigs();

        return $fixer;
    }

    /**
     * @param \Symfony\CS\Fixer $fixer
     * @param int               $level
     * @param string[]          $fixers
     *
     * @return \Symfony\CS\FixerInterface[]
     */
    private function resolveFixers($fixer, $level, $fixers)
    {
        $resolver = new \Symfony\CS\ConfigurationResolver();
        $resolver
            ->setAllFixers($fixer->getFixers())
            ->setConfig(
                \Symfony\CS\Config\Config::create()
                    ->level($level)
                    ->fixers($fixers)
            )
            ->resolve();

        return $resolver->getFixers();
    }
}
