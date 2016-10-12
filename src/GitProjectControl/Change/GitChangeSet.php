<?php

namespace uuf6429\GitProjectControl\Change;

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
     * @param string $filter See https://git-scm.com/docs/git-diff (--diff-filter)
     *
     * @return FileList
     */
    protected static function getFiltered($filter = null)
    {
        return new FileList(
            function () use ($filter) {
                $output = [];
                $filter = $filter ? ('--diff-filter=' . escapeshellarg($filter)) : '';
                exec("git diff --cached --name-status $filter", $output);

                return array_unique(
                    array_map(
                        function ($line) {
                            return trim(substr($line, 1));
                        },
                        $output
                    )
                );
            }
        );
    }
}
