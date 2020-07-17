<?php

namespace FosterMade\Rokanan\Console;

use FosterMade\Rokanan\Command;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('Rokanan', '0.9.0');
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), [
            new Command\SystemCheckCommand(),
            new Command\InitializeProjectCommand(),
            new Command\ConnectCommand(),
            new Command\RunCommand(),
            new Command\TrustCertificateCommand(),
        ]);
    }
}
