<?php

namespace uuf6429\ElderBrother;

class GitProjectTest extends BaseProjectTest
{
    public function setUp()
    {
        $this->markTestSkipped('To be enabled when git (un)install works.');
        parent::setUp();
    }

    public function testInstallation()
    {
        $this->assertCommandSuccessful('git init');
        $this->assertCommandSuccessful(self::getEbCmd() . 'install');

        // TODO ensure git hook is as expected

        $config = sprintf(
            '<%s return [%s => [%s]];',
            '?php',
            Event\Git::class . '::PRE_COMMIT',
            sprintf(
                'new %s( %s::getAdded()->name("/.php$/") )',
                Action\PhpLinter::class,
                Change\GitChangeSet::class
            )
        );
        $this->assertNotFalse(file_put_contents('.brother.php', $config));
    }

    /**
     * @depends testInstallation
     */
    public function testCommitingGoodCode()
    {
        $this->assertNotFalse(file_put_contents('test1.php', '<php echo "Hi!";'));

        $this->assertCommandSuccessful('git add .');
        $this->assertCommandSuccessful('git commit -m test1');
    }

    /**
     * @depends testCommitingGoodCode
     */
    public function testCommitingBadCode()
    {
        $this->assertNotFalse(file_put_contents('test2.php', '<php ec ho'));

        $this->assertCommandSuccessful('git add .');
        $this->assertCommand('git commit -m test2', 1, ['zxvzxz']);
    }

    /**
     * @depends testCommitingGoodCode
     * @depends testCommitingBadCode
     */
    public function testUninstallation()
    {
        $this->assertCommandSuccessful(self::getEbCmd() . 'uninstall');

        // TODO ensure git hook is as expected
    }
}
