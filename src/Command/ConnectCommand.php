<?php

namespace FosterMade\Rokanan\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConnectCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('connect')
            ->setAliases(['ssh'])
            ->setDescription('Opens a terminal session inside the provisioned VM')
            ->setHelp(
                <<<EOS
The <info>connect</info> command will open a terminal session inside the
provisioned VM. It is essentially a wrapper around `vagrant ssh` to retain
control over environment variables and run any pre/post tasks.

<info>rokanan connect</info>

EOS
            )
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $process = $this->createProcess(['vagrant', 'ssh']);
        $process->run();

        exit($process->getExitCode());
    }
}
