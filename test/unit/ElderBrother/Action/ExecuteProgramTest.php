<?php

namespace uuf6429\ElderBrother\Action;

use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ExecuteProgramTest extends \PHPUnit_Framework_TestCase
{
    public function testThatCrossPlatformProgramRuns()
    {
        $action = new ExecuteProgram(
            'Run "dir"',
            'dir'
        );

        $action->execute(new Input\StringInput(''), new Output\NullOutput());

        $this->assertSame('Run "dir" (ExecuteProgram)', $action->getName());
    }

    public function testThatRunningFictitiousProgramCausesException()
    {
        $action = new ExecuteProgram(
            'Run "a-non-existent-program"',
            'a-non-existent-program'
        );

        $this->setExpectedException(ProcessFailedException::class);

        $action->execute(new Input\StringInput(''), new Output\NullOutput());
    }
}
