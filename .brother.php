<?php

use uuf6429\ElderBrother\Action;
use uuf6429\ElderBrother\Event;
use uuf6429\ElderBrother\Change\GitChangeSet;

return [
    Event\Git::PRE_COMMIT => [
        1 => new Action\PhpLinter(
                GitChangeSet::getAddedCopiedModified()
                    ->name('*.php')
            ),
        2 => new Action\PhpCsFixer(
                GitChangeSet::getAddedCopiedModified()
                    ->name('*.php')
            ),
        3 => new Action\ExecuteProgram(
                'Generate Documentation',
                'php -f tools/docgen.php'
            )
    ]
];
