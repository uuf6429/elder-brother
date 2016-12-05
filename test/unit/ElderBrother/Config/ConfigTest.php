<?php

namespace uuf6429\ElderBrother\Config;

use Psr\Log\NullLogger;
use uuf6429\ElderBrother\Action;
use uuf6429\ElderBrother\Change;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigMergingFromDifferentSources()
    {
        $testFile = tempnam(sys_get_temp_dir(), 'test');
        $content = sprintf(
            '<?php return ["event1" => [ new %s(%s::get()) ]];',
            Action\PhpLinter::class,
            Change\FullChangeSet::class
        );
        $this->assertNotFalse(file_put_contents($testFile, $content));

        $cfg = new Config();
        $cfg->loadFromFile($testFile, new NullLogger());
        $cfg->loadFromArray(
            [
                'event2' => [
                    new Action\PhpLinter(Change\FullChangeSet::get()),
                ],
                'event1' => [
                    new Action\RiskyFiles(Change\FullChangeSet::get(), ''),
                    new Action\ForbiddenFiles(Change\FullChangeSet::get(), ''),
                ],
            ],
            new NullLogger()
        );

        $this->assertSame(
            ['event1', 'event2'],
            array_keys($cfg->getAllEventActions())
        );
        $this->assertSame(
            [Action\RiskyFiles::class, Action\ForbiddenFiles::class],
            array_map('get_class', $cfg->getActionsForEvent('event1'))
        );
        $this->assertSame(
            [Action\PhpLinter::class],
            array_map('get_class', $cfg->getActionsForEvent('event2'))
        );

        $this->assertNotFalse(unlink($testFile));
    }
}
