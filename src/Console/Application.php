<?php

namespace FosterMade\Rokanan\Console;

use FosterMade\Rokanan\Command;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('Rokanan', '0.9.0');
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), [
            new Command\SystemCheckCommand(),
            new Command\InitializeProjectCommand(),
            new Command\ConnectCommand(),
            new Command\RunCommand(),
        ]);
    }
}
