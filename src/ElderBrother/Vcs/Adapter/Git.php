<?php

namespace uuf6429\ElderBrother\Vcs\Adapter;

use uuf6429\ElderBrother\Event\Git as GitEvent;

class Git extends Adapter
{
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
                rename($file, $file . '.bak');
            }

            // create new hook file
            file_put_contents(
                $file,
                sprintf(
                    '#!/bin/sh%sphp -f %s -- run -e %s%s',
                    PHP_EOL,
                    escapeshellarg(ELDER_BROTHER_BIN),
                    escapeshellarg($event),
                    PHP_EOL
                )
            );
            chmod($file, 0755);
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
            unlink($file);

            // restore backed up hook
            if (file_exists($file . '.bak')) {
                rename($file . '.bak', $file);
                chmod($file, 0755);
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
        return PROJECT_ROOT
            . '.git' . DIRECTORY_SEPARATOR
            . 'hooks' . DIRECTORY_SEPARATOR;
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
