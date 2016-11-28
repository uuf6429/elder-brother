<?php

namespace uuf6429\ElderBrother\Action;

use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use uuf6429\ElderBrother\Config\ConfigInterface;

class ExecuteCodeTest extends \PHPUnit_Framework_TestCase
{
    public function testThatExceptionIsPropagated()
    {
        $action = new ExecuteCode(
            'Do something',
            function ($config, $input, $output) {
                $this->assertInstanceOf(ConfigInterface::class, $config);
                $this->assertInstanceOf(Input\InputInterface::class, $input);
                $this->assertInstanceOf(Output\OutputInterface::class, $output);

                throw new \RuntimeException('Testing');
            }
        );

        $action->setConfig(
            $this->getMockBuilder(ConfigInterface::class)
                ->disableOriginalConstructor()
                ->getMock()
        );

        $this->setExpectedException(\RuntimeException::class, 'Testing');

        $action->execute(new Input\StringInput(''), new Output\NullOutput());
    }
}
