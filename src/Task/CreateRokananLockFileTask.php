<?php

namespace FosterMade\Rokanan\Task;

use FosterMade\Rokanan\Command\Command;
use Symfony\Component\Process\Process;

class CreateRokananLockFileTask
{
    public function __invoke(Command $context)
    {
        $process = new Process('git rev-parse HEAD', $context->cwd);
        $head = trim($process->mustRun()->getOutput());
        $context->filesystem->dumpFile($context->cwd.'/rokanan.lock', $head.PHP_EOL);
    }
}
