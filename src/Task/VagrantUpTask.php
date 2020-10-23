<?php

namespace FosterMade\Rokanan\Task;

use FosterMade\Rokanan\Command\Command;
use Symfony\Component\Process\Process;

class VagrantUpTask
{
    /**
     * @throws \Exception
     */
    public function __invoke(Command $context)
    {
        $process = new Process(["vagrant", "up"], $context->cwd, null, null, INF);
        $process->setPty(true);
        $process->setTty(true);

        return $process->mustRun()->getExitCode();
    }
}
