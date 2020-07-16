<?php

namespace FosterMade\Rokanan\Task;

use FosterMade\Rokanan\Command\Command;

class AddGitIgnorePatternsTask
{
    /**
     * @var string
     */
    const DELIMITER = <<<EOS

###> rokanan ###
%s
###< rokanan ###
EOS;


    /**
     * @var array
     */
    protected $ansibleIgnorePatterns = [
        '*.retry',
    ];

    /**
     * @var array
     */
    protected $projectIgnorePatterns = [
        'ubuntu-*-console.log',
    ];

    public function __invoke(Command $context)
    {
        $context->filesystem->dumpFile(
            $context->cwd.'/ansible/.gitignore',
            sprintf(static::DELIMITER, join("\n", $this->ansibleIgnorePatterns))
        );

        $context->filesystem->appendToFile(
            $context->cwd.'/.gitignore',
            sprintf(static::DELIMITER, join("\n", $this->projectIgnorePatterns))
        );
    }
}
