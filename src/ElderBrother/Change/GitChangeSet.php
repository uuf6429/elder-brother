<?php

namespace uuf6429\ElderBrother\Change;

use Symfony\Component\Process\Process;

class GitChangeSet
{
    /**
     * Returns all files that have been created, changed, moved or deleted.
     *
     * @return FileList
     */
    public static function getAllChanges()
    {
        return static::getFiltered();
    }

    /**
     * Returns only the files created by this changeset.
     *
     * @return FileList
     */
    public static function getAdded()
    {
        return static::getFiltered('A');
    }

    /**
     * Returns files to be deleted by this changeset.
     *
     * @return FileList
     */
    public static function getRemoved()
    {
        return static::getFiltered('D');
    }

    /**
     * Returns all modified files.
     *
     * @return FileList
     */
    public static function getModified()
    {
        return static::getFiltered('M');
    }

    /**
     * Returns all files that have been copied or renmaed by this changeset.
     *
     * @return FileList
     */
    public static function getCopiedOrMoved()
    {
        return static::getFiltered('CR');
    }

    /**
     * Returns all changed files except those that have been deleted.
     *
     * @return FileList
     */
    public static function getAddedCopiedModified()
    {
        return static::getFiltered('ACMR');
    }

    /**
     * @param string|null $filter See https://git-scm.com/docs/git-diff (--diff-filter)
     *
     * @return FileList
     */
    protected static function getFiltered($filter = null)
    {
        return new FileList(
            __METHOD__ . '(' . $filter . ')',
            function () use (&$filter) {
                $command = 'git diff -z --cached --name-only';

                if ($filter) {
                    $command .= ' --diff-filter=' . escapeshellarg($filter);
                }

                $process = new Process($command);
                $process->mustRun();

                return array_filter(
                    array_unique(
                        explode("\0", $process->getOutput())
                    )
                );
            }
        );
    }
}
