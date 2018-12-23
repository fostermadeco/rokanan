<?php

namespace FosterMade\Rokanan\Task;

use FosterMade\Rokanan\Command\Command;

class CreateVagrantfileTask
{
    public function runInContext(Command $context)
    {
        $context->filesystem->copy(
            $context->root.'/dependencies/vagrant/Vagrantfile',
            $context->cwd.'/Vagrantfile'
        );
    }
}
