<?php

require_once __DIR__ . '/../vendor/autoload.php';

use \phpDocumentor\Reflection\DocBlockFactory;

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

        /** @var \uuf6429\ElderBrother\Action\ActionAbstract $object */
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

        $secActions[] = '### ' . ucwords($object->getName());
        $secActions[] = '';
        $params = $docBlock->getTagsByName('param');
        if (count($params)) {
            $secActions[] = '| Parameter  | Type | Description |';
            $secActions[] = '|------------|------|-------------|';
            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Param $param */
            foreach ($params as $param) {
                $secActions[] = sprintf(
                    '| `$%s` | %s | %s |',
                    $param->getVariableName(),
                    trim($param->getType())
                        ? '`' . trim($param->getType()) . '`'
                        : '*unknown*',
                    trim($param->getDescription()) ?: '*None*'
                );
            }
        }

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
