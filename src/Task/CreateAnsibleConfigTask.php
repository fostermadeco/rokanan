<?php

namespace FosterMade\Rokanan\Task;

use FosterMade\Rokanan\Command\Command;
use Symfony\Component\Yaml\Yaml;

class CreateAnsibleConfigTask
{
    /**
     * @param Command $context
     */
    public function __invoke(Command $context)
    {
        $config = Yaml::parseFile($context->root.'/dependencies/ansible/config.yaml');
        $config['defaults']['roles_path'] = ':${COMPOSER_HOME}/vendor/fostermadeco/rokanan/dependencies/ansible/roles:~/.composer/vendor/fostermadeco/rokanan/dependencies/ansible/roles:/etc/ansible/roles:';
        $configFile = $context->cwd.'/ansible.cfg';

        foreach ($config as $section => $values) {
            $context->filesystem->appendToFile($configFile, "[{$section}]".PHP_EOL);

            foreach ($values as $key => $value) {
                $context->filesystem->appendToFile($configFile, "{$key} = {$value}".PHP_EOL);
            }
        }
    }
}
