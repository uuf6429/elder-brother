<?php

namespace uuf6429\ElderBrother;

class NoVcsTest extends BaseProjectTest
{
    const CUSTOM_EVENT = 'custom:test';

    public function testSetup()
    {
        $config = sprintf(
            '<%s return ["%s" => [%s]];',
            '?php',
            self::CUSTOM_EVENT,
            sprintf(
                'new %s( %s::get() )',
                Action\PhpLinter::class,
                Change\FullChangeSet::class
            )
        );
        $this->assertNotFalse(file_put_contents('.brother.php', $config));
    }

    /**
     * @depends testSetup
     */
    public function testGoodCode()
    {
        $this->assertNotFalse(file_put_contents('test1.php', '<?php echo "Hi!";'));

        $this->assertCommandSuccessful(self::getEbCmd() . 'run --no-progress -e ' . self::CUSTOM_EVENT);
    }

    /**
     * @depends testGoodCode
     */
    public function testBadCode()
    {
        $this->assertNotFalse(file_put_contents('test2.php', '<?php 3ch"o'));

        $this->assertCommand(self::getEbCmd() . 'run --no-progress -e ' . self::CUSTOM_EVENT, 1);
    }
}
