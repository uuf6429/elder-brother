<?php

namespace uuf6429\ElderBrother;

class GitProjectTest extends BaseProjectTest
{
    public function testInstallation()
    {
        $this->assertCommandSuccessful('git init');
        $this->assertCommandSuccessful('git config user.email "john.doe@umbrella.mil"');
        $this->assertCommandSuccessful('git config user.name "John Doe"');

        $this->assertNotFalse(
            file_put_contents('.git/hooks/post-commit', 'echo "OK"'),
            'Create fake "old" hook.'
        );

        $this->assertCommandSuccessful(self::getEbCmd() . 'install');

        $this->assertTrue(
            file_exists('.git/hooks/pre-commit'),
            'Pre-commit hook was not installed successfully.'
        );

        $this->assertTrue(
            file_exists('.git/hooks/post-commit.bak'),
            'Ensure "old" hook has been backed up.'
        );

        $config = sprintf(
            '<%s return [%s => [%s]];',
            '?php',
            Event\Git::class . '::PRE_COMMIT',
            sprintf(
                'new %s( %s::getAdded() )',
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
        $this->assertNotFalse(file_put_contents('test2.php', '<?php 3ch"o'));

        $this->assertCommandSuccessful('git add .');
        $this->assertCommand('git commit -m test2', 1);
    }

    /**
     * @depends testCommitingGoodCode
     * @depends testCommitingBadCode
     */
    public function testUninstallation()
    {
        $this->assertCommandSuccessful(self::getEbCmd() . 'uninstall');

        $this->assertFalse(
            file_exists('.git/hooks/pre-commit'),
            'Pre-commit hook was not uninstalled successfully.'
        );

        $this->assertTrue(
            file_exists('.git/hooks/post-commit'),
            'Ensure "old" hook has been restored.'
        );
    }
}
