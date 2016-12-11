<?php

use uuf6429\ElderBrother\Action;
use uuf6429\ElderBrother\Event;
use uuf6429\ElderBrother\Change\GitChangeSet;

return [
    Event\Git::PRE_COMMIT => [
        1 => new Action\PhpLinter(
                GitChangeSet::getAddedCopiedModified()
                    ->notPath('/vendor')
                    ->name('*.php')
            ),
        2 => new Action\PhpCsFixer(
                GitChangeSet::getAddedCopiedModified()
                    ->notPath('/vendor')
                    ->name('*.php'),
                Action\PhpCsFixer::SYMFONY_LEVEL,
                [
                    'array_element_no_space_before_comma',
                    'array_element_white_space_after_comma',
                    'blankline_after_open_tag',
                    'concat_with_spaces',
                    'lowercase_cast',
                    'multiline_array_trailing_comma',
                    '-namespace_no_leading_whitespace',
                    'ordered_use',
                    'phpdoc_order',
                    'remove_leading_slash_use',
                    'remove_lines_between_uses',
                    'return',
                    'short_array_syntax',
                    'single_quote',
                    'standardize_not_equal',
                    'ternary_spaces',
                    'unused_use',
                ]
            ),
        3 => new Action\ExecuteProgram(
                'Generate Documentation',
                'php -f tools/docgen.php'
            )
    ],
];
