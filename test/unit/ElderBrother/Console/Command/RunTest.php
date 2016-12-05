<?php

namespace uuf6429\ElderBrother\Console\Command;

use Psr\Log\NullLogger;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use uuf6429\ElderBrother\Action;
use uuf6429\ElderBrother\Change;
use uuf6429\ElderBrother\Config\Config;
use uuf6429\ElderBrother\Console\Command;

class RunTest extends \PHPUnit_Framework_TestCase
{
    const CUSTOM_EVENT = 'custom:test';

    public function testRunWithoutActions()
    {
        $inp = $this->getInput();
        $out = new BufferedOutput();
        $cmd = $this->getRunCommand([]);

        $exit = $cmd->run($inp, $out);
        $this->assertSame(0, $exit, 'Command output: ' . $out->fetch());
    }

    public function testGoodCode()
    {
        $testFile = tempnam(sys_get_temp_dir(), 'test');
        $this->assertNotFalse(file_put_contents($testFile, '<?php echo "Hi!";'));

        $inp = $this->getInput();
        $out = new BufferedOutput();
        $cmd = $this->getRunCommand(
            [
                new Action\PhpLinter(
                    new Change\FileList(
                        'good-code-test',
                        function () use ($testFile) {
                            return [new Change\FileInfo($testFile, dirname($testFile), basename($testFile))];
                        }
                    )
                ),
            ]
        );

        $exit = $cmd->run($inp, $out);
        $this->assertSame(0, $exit, 'Command output: ' . $out->fetch());

        $this->assertNotFalse(unlink($testFile));
    }

    public function testBadCode()
    {
        $testFile = tempnam(sys_get_temp_dir(), 'test');
        $this->assertNotFalse(file_put_contents($testFile, '<?php 3ch"o'));

        $inp = $this->getInput();
        $out = new BufferedOutput();
        $cmd = $this->getRunCommand(
            [
                new Action\PhpLinter(
                    new Change\FileList(
                        'bad-code-test',
                        function () use ($testFile) {
                            return [new Change\FileInfo($testFile, dirname($testFile), basename($testFile))];
                        }
                    )
                ),
            ]
        );

        $exit = $cmd->run($inp, $out);
        $this->assertSame(1, $exit, 'Command output: ' . $out->fetch());

        $this->assertNotFalse(unlink($testFile));
    }

    /**
     * @param Action\ActionAbstract[] $actions
     *
     * @return Command\Run
     */
    protected function getRunCommand($actions)
    {
        $cfg = new Config();
        $log = new NullLogger();
        $cfg->loadFromArray([self::CUSTOM_EVENT => $actions], $log);
        $cmd = new Command\Run();
        $cmd->setConfig($cfg);
        $cmd->setLogger($log);
        $cmd->setApplication(new Application());

        return $cmd;
    }

    /**
     * @return ArgvInput
     */
    protected function getInput()
    {
        return new ArgvInput(['run', '-e', self::CUSTOM_EVENT, '--no-progress', '--no-ansi']);
    }
}
