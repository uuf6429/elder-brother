<?php

namespace uuf6429\GitProjectControl\Action;

use uuf6429\GitProjectControl\Change;

class FileValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string[]        $files
     * @param string          $reason
     * @param null|\Exception $expectedException
     *
     * @dataProvider validationScenarioDataProvider
     */
    public function testValidationScenario($files, $reason, $expectedException)
    {
        $fileList = new Change\FileList(
            'cache' . mt_rand(),
            function () use ($files) {
                return $files;
            }
        );
        $action = new FileValidator($fileList, $reason);

        if ($expectedException) {
            $this->setExpectedException(
                get_class($expectedException),
                $expectedException->getMessage()
            );
        }

        $action->execute($this->getInputMock(), $this->getOutputMock());

        $this->assertNull($expectedException, 'No exception should be thrown.');
    }

    /**
     * @return array
     */
    public function validationScenarioDataProvider()
    {
        return [
            'no files should not cause exception' => [
                '$files' => [],
                '$reason' => '',
                '$expectedException' => null,
            ],
            'fail validation for one file' => [
                '$files' => ['A:\\file\\that\\failed.txt'],
                '$reason' => 'Files from floppy disks are not allowed!',
                '$expectedException' => new \RuntimeException(
                    'The following files failed validation:' . PHP_EOL .
                    '- A:\file\that\failed.txt' . PHP_EOL .
                    'Files from floppy disks are not allowed!'
                ),
            ],
            'fail validation for some files, without reason' => [
                '$files' => ['test/file1.txt', 'test/file2.txt'],
                '$reason' => '',
                '$expectedException' => new \RuntimeException(
                    'The following files failed validation:' . PHP_EOL .
                    '- test/file1.txt' . PHP_EOL .
                    '- test/file2.txt'
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
        return $this->getMockBuilder(
                \Symfony\Component\Console\Output\OutputInterface::class
            )->getMock();
    }
}
