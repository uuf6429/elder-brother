<?php

namespace uuf6429\GitProjectControl\Action;

use uuf6429\GitProjectControl\Change;

class PhpLinterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array           $fileContents
     * @param null|\Exception $expectedException
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

            if ($expectedException) {
                $expectedMessage = str_replace(
                    array_keys($fileContents),
                    $createdFiles,
                    $expectedException->getMessage()
                );

                $this->setExpectedException(
                    get_class($expectedException),
                    $expectedMessage
                );
            }

            $action->execute($this->getInputMock(), $this->getOutputMock());

            $this->assertNull($expectedException, 'No exception should be thrown.');

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
            'a file with valid synax' => [
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
                    '- <options=underline>file2.php</>:' . PHP_EOL .
                    ' - Parse error: syntax error, unexpected \'"\', expecting \',\' or \';\' in file2.php on line 1'
                ),
            ],
            'files with some errors' => [
                '$fileContents' => [
                    'file3.php' => '<?php echo Test1"; ',
                    'file4.php' => '<?php echo "Test1"; ',
                    'file5.php' => '<?php ecsho "Test1"; ',
                    'file6.php' => '',
                    'file7.php' => '<?php return 0; ',
                    'file8.php' => '<?php dgsda!^hfd ',
                ],
                '$expectedException' => new \RuntimeException(
                    'PhpLinter failed for the following file(s):' . PHP_EOL .
                    '- <options=underline>file3.php</>:' . PHP_EOL .
                    ' - Parse error: syntax error, unexpected \'"\', expecting \',\' or \';\' in file3.php on line 1' . PHP_EOL .
                    '- <options=underline>file5.php</>:' . PHP_EOL .
                    ' - Parse error: syntax error, unexpected \'"Test1"\' (T_CONSTANT_ENCAPSED_STRING) in file5.php on line 1' . PHP_EOL .
                    '- <options=underline>file8.php</>:' . PHP_EOL .
                    ' - Parse error: syntax error, unexpected \'!\' in file8.php on line 1'
                ),
            ],
        ];
    }

    /**
     * @return \Symfony\Component\Console\Input\InputInterface
     */
    protected function getInputMock()
    {
        return $this->getMockBuilder(
                \Symfony\Component\Console\Input\InputInterface::class
            )->getMock();
    }

    /**
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    protected function getOutputMock()
    {
        return new \Symfony\Component\Console\Output\NullOutput();
    }

    /**
     * @param string $fileContents
     */
    protected function createFiles($fileContents)
    {
        return array_values(
            array_map(
                function ($index, $content) {
                    $filename = tempnam(sys_get_temp_dir(), "tf$index");
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
            @unlink($file);
        }
    }
}
