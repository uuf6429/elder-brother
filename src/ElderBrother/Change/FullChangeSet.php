<?php

namespace uuf6429\ElderBrother\Change;

class FullChangeSet
{
    /**
     * @return FileList
     */
    public static function get()
    {
        return new FileList(
            __METHOD__ . '()',
            function () {
                $files = iterator_to_array(
                    new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator(
                            getcwd(),
                            \RecursiveDirectoryIterator::SKIP_DOTS
                        ),
                        \RecursiveIteratorIterator::CHILD_FIRST
                    )
                );

                return array_map(
                    function (\SplFileInfo $file) {
                        return $file->getRealPath();
                    },
                    $files
                );
            }
        );
    }
}
