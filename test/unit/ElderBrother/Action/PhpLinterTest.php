<?php

namespace uuf6429\ElderBrother\Action;

use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use uuf6429\ElderBrother\Change;

class PhpLinterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array           $fileContents
     * @param null|\Exception $expectedException
     *
     * @throws \Exception
     *
     * @dataProvider validationScenarioDataProvider
     */
    public function testValidationScenario($fileContents, $expectedException)
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
            $action = new PhpLinter($fileList);

            try {
                $action->execute($this->getInputMock(), $this->getOutputMock());

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

                // replace platform-specific text with general text
                $aliases = [
                    'PHP Parse error:  ',
                    'Parse error: ',
                    'Fatal error: Uncaught Error: ',
                    'Fatal error: ',
                ];
                $message = str_replace($aliases, '????: ', $message);

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
                '$expectedException' => null,
            ],
            'a file with valid syntax' => [
                '$fileContents' => [
                    'file1.php' => '<?php echo "Test1"; ',
                ],
                '$expectedException' => null,
            ],
            'a file with a syntax error' => [
                '$fileContents' => [
                    'file2.php' => '<?php echo Test1"; ',
                ],
                '$expectedException' => new \RuntimeException(
                    'PhpLinter failed for the following file(s):' . PHP_EOL .
                    '- file2.php:' . PHP_EOL .
                    ' - ????: syntax error, unexpected \'"\', expecting \',\' or \';\' in file2.php on line 1'
                ),
            ],
            'files with some errors' => [
                '$fileContents' => [
                    'file3.php' => '<?php echo Test1"; ',
                    'file4.php' => '<?php echo "Test1"; ',
                    'file6.php' => '',
                    'file7.php' => '<?php return 0; ',
                    'file8.php' => '<?php dgsda!^hfd ',
                ],
                '$expectedException' => new \RuntimeException(
                    'PhpLinter failed for the following file(s):' . PHP_EOL .
                    '- file3.php:' . PHP_EOL .
                    ' - ????: syntax error, unexpected \'"\', expecting \',\' or \';\' in file3.php on line 1' . PHP_EOL .
                    '- file8.php:' . PHP_EOL .
                    ' - ????: syntax error, unexpected \'!\' in file8.php on line 1'
                ),
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
