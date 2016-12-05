<?php

namespace uuf6429\ElderBrother\Action;

use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use uuf6429\ElderBrother\Change;

class PhpCsFixerOldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array           $fileContents
     * @param null|array      $expectedFileContents
     * @param null|\Exception $expectedException
     *
     * @throws \Exception
     *
     * @dataProvider validationScenarioDataProvider
     */
    public function testValidationScenario($fileContents, $expectedFileContents = null, $expectedException = null)
    {
        $createdFiles = [];

        try {
            $createdFiles = $this->createFiles($fileContents);

            $fileList = new Change\FileList(
                'cache' . mt_rand(),
                function () use ($createdFiles) {
                    return $createdFiles;
                }
            );

            $binFile = realpath(__DIR__ . '/../../../../vendor/friendsofphp/php-cs-fixer/php-cs-fixer');
            $this->assertNotFalse($binFile, 'PHP-CS-Fixer executable could not be located (cwd: ' . getcwd() . ').');

            $configFile = tempnam(sys_get_temp_dir(), 'pcc');
            $this->assertNotFalse(
                file_put_contents(
                    $configFile,
                    '<?php return Symfony\CS\Config\Config::create()'
                    . '->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)'
                    . '->fixers(["linefeed"]);'
                ),
                'PHP-CS-Fixer config file could not be saved: ' . $configFile
            );

            $action = new PhpCsFixerOld($fileList, $binFile, $configFile, false);

            try {
                $this->assertTrue($action->isSupported());
                $action->execute($this->getInputMock(), $this->getOutputMock());

                if (!is_null($expectedFileContents)) {
                    $this->assertEquals(
                        $expectedFileContents,
                        array_combine(
                            array_keys($fileContents),
                            array_map(
                                function ($file) {
                                    return file($file, FILE_IGNORE_NEW_LINES);
                                },
                                $createdFiles
                            )
                        )
                    );
                }

                $this->assertNull($expectedException, 'No exception should be thrown.');
            } catch (\Exception $ex) {
                if (!$expectedException) {
                    throw $ex;
                }

                // replace virtual filenames with fake filenames
                $message = str_replace(
                    $createdFiles,
                    array_keys($fileContents),
                    $ex->getMessage()
                );

                // do some asserting :)
                $this->assertSame(get_class($expectedException), get_class($ex));
                $this->assertSame($expectedException->getMessage(), $message);
            }

            $this->removeFiles($createdFiles);
        } catch (\Exception $ex) {
            $this->removeFiles($createdFiles);
            throw $ex;
        }
    }

    /**
     * @return array
     */
    public function validationScenarioDataProvider()
    {
        return [
            'no files should not cause exception' => [
                '$fileContents' => [],
                '$expectedFileContents' => [],
                '$expectedException' => null,
            ],
            'a file with an inline class and method' => [
                '$fileContents' => [
                    'file1.php' => '<?php class Test {public function sayHello(){echo "Hello!";}} ',
                ],
                '$expectedFileContents' => [
                    'file1.php' => [
                        '<?php class Test',
                        '{',
                        '    public function sayHello()',
                        '    {',
                        '        echo \'Hello!\';',
                        '    }',
                        '}',
                    ],
                ],
                '$expectedException' => null,
            ],
            'a file with a syntax error' => [
                '$fileContents' => [
                    'file2.php' => '<?php *e(cho Test1"; ',
                ],
                '$expectedFileContents' => [
                    'file2.php' => [
                        '<?php *e(cho Test1"; ',
                    ],
                ],
                '$expectedException' => null,
            ],
        ];
    }

    /**
     * @return Input\InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getInputMock()
    {
        return $this->getMockBuilder(Input\InputInterface::class)
            ->getMock();
    }

    /**
     * @return Output\OutputInterface
     */
    protected function getOutputMock()
    {
        return new Output\NullOutput();
    }

    /**
     * @param array<string, string> $fileContents
     *
     * @return string[]
     */
    protected function createFiles($fileContents)
    {
        return array_values(
            array_map(
                function ($index, $content) {
                    $filename = tempnam(sys_get_temp_dir(), $index);
                    file_put_contents($filename, $content);

                    return $filename;
                },
                array_keys($fileContents),
                $fileContents
            )
        );
    }

    /**
     * @param string[] $files
     */
    protected function removeFiles($files)
    {
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
