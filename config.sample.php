<?php

use uuf6429\GitProjectControl\Action;
use uuf6429\GitProjectControl\Change\GitChangeSet;

return [
    10 => new Action\PhpLinter(
            GitChangeSet::getAddedCopiedModified()
                ->endingWith('.php')
        ),
    20 => new Action\PhpCsFixer(
            GitChangeSet::getAddedCopiedModified()
                ->endingWith('.php')
        ),
    30 => new Action\FlleNameMustMatch(
            GitChangeSet::getAdded()
                ->startingWith(
                    [
                        'tools\\migrations\\',
                        'tools\\rollbacks\\',
                    ]
                ),
            '/^GPC-\d+-\d{2}-\d{2}-\d{4}[a-z]?\.(sql|php)$/'
        ),
];
