<?php

namespace FosterMade\Rokanan\Command;

use FosterMade\Rokanan\Task\TrustCertificateTask;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TrustCertificateCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('trust-certificate')
            ->setAliases(['trust-cert'])
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

       return (new TrustCertificateTask())->runInContext($this);
    }
}
