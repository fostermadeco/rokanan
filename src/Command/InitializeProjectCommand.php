<?php

namespace FosterMade\Rokanan\Command;

use FosterMade\Rokanan\Task\AddGitIgnorePatternsTask;
use FosterMade\Rokanan\Task\BuildHostFilesTask;
use FosterMade\Rokanan\Task\BuildProvisionFileTask;
use FosterMade\Rokanan\Task\CheckVagrantStatusTask;
use FosterMade\Rokanan\Task\CreateAnsibleConfigTask;
use FosterMade\Rokanan\Task\CreateRokananLockFileTask;
use FosterMade\Rokanan\Task\CreateVagrantfileTask;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeProjectCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('initialize')
            ->setAliases(['init'])
        ;
    }

    /**
     * @inheritDoc
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        (new CheckVagrantStatusTask())($this);
        (new BuildHostFilesTask())($this);
        (new BuildProvisionFileTask())($this);
        (new CreateAnsibleConfigTask())($this);
        (new CreateVagrantfileTask())($this);
        (new CreateRokananLockFileTask())($this);
        (new AddGitIgnorePatternsTask())($this);
    }
}
