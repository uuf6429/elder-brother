<?php

namespace uuf6429\ElderBrother\Console;

use Symfony\Component\Console\Application as ConsoleApplication;

class Application extends ConsoleApplication
{
    public function __construct()
    {
        error_reporting(-1);

        parent::__construct('Elder Brother', '1.0.0');

        $this->add(new Command\Run());
        $this->add(new Command\GitInstall());
        //$this->add(new Command\GitUninstall());
    }
}
