<?php

namespace FosterMade\Rokanan\Task;

use FosterMade\Rokanan\Command\Command;
use Symfony\Component\Process\Process;

class CheckVagrantStatusTask
{
    /**
     * @throws \Exception
     */
    public function runInContext(Command $context)
    {
        $process = new Process('vagrant status --machine-readable | awk -F, \'/state-human-short/ { print $NF }\'', $context->cwd);
        $status = trim($process->mustRun()->getOutput());

        if (!in_array($status, ['', 'not created'])) {
            throw new \Exception(
                'An existing Vagrant environment has been detected in '.$this->cwd.'.'.PHP_EOL.
                'Please destroy it before initializing it as a Rokanan project.'
            );
        }
    }
}
