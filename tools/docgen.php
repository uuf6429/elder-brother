<?php

require_once __DIR__ . '/../vendor/autoload.php';

use \phpDocumentor\Reflection\DocBlock\Tags\Param;
use \phpDocumentor\Reflection\DocBlockFactory;
use \Symfony\Component\Debug;
use \uuf6429\ElderBrother\Action\ActionAbstract;

Debug\ErrorHandler::register();
Debug\ExceptionHandler::register();

$docBlockFactory = DocBlockFactory::createInstance();
$tocActions = [];
$secActions = [];

foreach (glob(__DIR__ . '/../src/ElderBrother/Action/*.php') as $file) {
    try {
        include_once $file;

        $class = '\\uuf6429\\ElderBrother\\Action\\' . basename($file, '.php');
        $reflector = new \ReflectionClass($class);
        if (!$reflector->isInstantiable()) {
            continue;
        }
        $constructor = $reflector->getMethod('__construct');
        $docBlock = $docBlockFactory->create($constructor);

        /** @var ActionAbstract $object */
        $object = $reflector->newInstanceWithoutConstructor();

        $tocActions[] = sprintf(
            '    - [%s](#%s)',
            ucwords($object->getName()),
            str_replace(
                [' ', '(', ')'],
                ['-', '', ''],
                strtolower($object->getName())
            )
        );
        $params = $docBlock->getTagsByName('param');
        $signature = 'new ' . $reflector->getShortName() . '(';
        foreach ($params as $i => $param) {
            /** @var Param $param */
            $isLast = $i == (count($params) - 1);
            $signature .= sprintf(
                "\n    %s\$%s%s%s%s",
                (bool) ($type = trim($param->getType())) ? "$type " : '',
                $param->getVariableName(),
                $isLast ? '' : ',',
                (bool) ($desc = trim($param->getDescription())) ? " // $desc" : '',
                $isLast ? "\n" : ''
            );
        }
        $signature .= ')';

        $secActions[] = sprintf(
            '### [%s](https://github.com/uuf6429/elder-brother/blob/master/src/ElderBrother/Action/%s.php)',
            ucwords($object->getName()),
            $reflector->getShortName()
        );
        $secActions[] = '';
        $secActions[] = "```php\n$signature\n```";
        $secActions[] = trim($docBlock->getSummary()) ?: '*No Summary*';
        $secActions[] = '';
    } catch (\Exception $ex) {
        echo $ex;
    }
}

// prepare variables to be replaced
$replacements = [
    '{{TOC_ACTIONS}}' => implode("\n", $tocActions),
    '{{SECTION_ACTIONS}}' => implode("\n", $secActions),
];

// overwrite readme
file_put_contents(
    __DIR__ . '/../README.md',
    str_replace(
        array_keys($replacements),
        array_values($replacements),
        file_get_contents(__DIR__ . '/template.md')
    )
);

echo 'Done.' . PHP_EOL;
