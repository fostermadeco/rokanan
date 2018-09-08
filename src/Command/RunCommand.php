<?php

namespace FosterMade\Rokanan\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('run')
            ->addArgument('subcommand', InputArgument::REQUIRED)
            ->setDescription('Runs an arbitrary command inside the provisioned VM')
            ->setHelp(
                <<<EOS
The <info>run</info> command will run an arbitrary subcommand inside the
provisioned VM. It is essentially a wrapper around `vagrant ssh -c [subcommand]`
to retain control over environment variables (like VAGRANT_USE_VAGRANT_TRIGGERS).

<info>rokanan run [subcommand]</info>

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

        $subcommand = "cd {$this->projectRoot};".$input->getArgument('subcommand');
        $process = $this->createVagrantProcess(['vagrant', 'ssh', '-c', $subcommand]);
        $process->run();
        exit($process->getExitCode());
    }
}
