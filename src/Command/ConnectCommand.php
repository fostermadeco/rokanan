<?php

namespace FosterMade\Rokanan\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ConnectCommand extends Command
{
    /**
     * {@inheritdoc}
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
control over environment variables (like VAGRANT_USE_VAGRANT_TRIGGERS) and
run any pre/post tasks.

<info>rokanan connect</info>

EOS
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $process = $this->createVagrantProcess('vagrant ssh');
        $process->run();
        exit($process->getExitCode());
    }
}
