<?php

namespace uuf6429\ElderBrother\Vcs\Adapter;

use Symfony\Component\Process\Process;
use uuf6429\ElderBrother\Event\Git as GitEvent;

class Git extends Adapter
{
    /**
     * @var string|null
     */
    protected $hookPath;

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return is_dir($this->getHookPath());
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        return file_exists($this->getInstallLockFile());
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $hookFiles = $this->getHookFiles();

        // create install lock
        fclose(fopen($this->getInstallLockFile(), 'x'));

        foreach ($hookFiles as $event => $file) {
            // back up existing hook
            if (file_exists($file)) {
                if (!rename($file, $file . '.bak')) {
                    throw new \RuntimeException("Could not back up hook file: $file");
                }
            }

            // create new hook file
            if (!file_put_contents(
                $file,
                sprintf(
                    '#!/bin/sh%sphp -f %s -- run -e %s%s',
                    PHP_EOL,
                    escapeshellarg(str_replace(PROJECT_ROOT, '', ELDER_BROTHER_BIN)),
                    escapeshellarg($event),
                    PHP_EOL
                )
            )) {
                throw new \RuntimeException("Could not create hook file: $file");
            }
            if (!chmod($file, 0755)) {
                throw new \RuntimeException("Could not make hook file executable: $file");
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall()
    {
        $hookFiles = $this->getHookFiles();

        foreach ($hookFiles as $file) {
            // remove hook file
            if (!unlink($file)) {
                throw new \RuntimeException("Could not remove hook file: $file");
            }

            // restore backed up hook
            if (file_exists($file . '.bak')) {
                if (!rename($file . '.bak', $file) || !chmod($file, 0755)) {
                    throw new \RuntimeException("Could not properly restore hook file: $file");
                }
            }
        }

        // remove install lock
        unlink($this->getInstallLockFile());
    }

    /**
     * @return string
     */
    protected function getHookPath()
    {
        if (!$this->hookPath) {
            $process = new Process('git rev-parse --git-dir', PROJECT_ROOT);
            $process->run();

            if (!$process->isSuccessful()) {
                $this->logger->warning(
                    sprintf(
                        "Failed to retrieve git project root:\n$ %s (exit: %d)\n> %s",
                        $process->getCommandLine(),
                        $process->getExitCode(),
                        implode("\n> ", explode(PHP_EOL, $process->getOutput()))
                    )
                );
            } else {
                $this->hookPath = realpath(trim($process->getOutput())) . '/hooks/';
            }
        }

        return $this->hookPath;
    }

    /**
     * @return string
     */
    protected function getInstallLockFile()
    {
        return $this->getHookPath() . 'ebi.lock';
    }

    /**
     * @return string[]
     */
    protected function getHookFiles()
    {
        $hookPath = $this->getHookPath();

        $result = [];
        foreach ((new \ReflectionClass(GitEvent::class))->getConstants() as $eventName) {
            $hook = explode(':', $eventName, 2);

            if (count($hook) === 2 && $hook[0] === 'git') {
                $result[$eventName] = $hookPath . $hook[1];
            }
        }

        return $result;
    }
}
