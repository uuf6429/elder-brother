<?php

use uuf6429\ElderBrother\Action;
use uuf6429\ElderBrother\Change\GitChangeSet;

return [
    10 => new Action\PhpLinter(
            GitChangeSet::getAddedCopiedModified()
                ->name('/.php$/')
        ),
    20 => new Action\PhpCsFixer(
            GitChangeSet::getAddedCopiedModified()
                ->name('/.php$/')
        ),
    30 => new Action\ForbiddenFiles(
            GitChangeSet::getAdded()
                ->name('/^tools\\(migrations|rollbacks)\\/')
                ->notName('/GPC-\d+-\d{2}-\d{2}-\d{4}[a-z]?\.(sql|php)$/'),
            'Migration/rollback files must match this format: GPC-NNNN-DD-MM-YYYY[a-z].[sql|php]'
        ),
];
