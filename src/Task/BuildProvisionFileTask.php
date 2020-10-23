<?php

namespace FosterMade\Rokanan\Task;

use FosterMade\Rokanan\Command\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class BuildProvisionFileTask
{
    /**
     * @var string
     */
    protected $rolesPath;

    /**
     * @var string
     */
    protected $anonymousRolesPath;

    public function __invoke(Command $context)
    {
        $context->output->writeln('<comment>Let’s build a provision file . . .</comment>');

        $provision = Yaml::parseFile($context->root.'/dependencies/ansible/provision.yaml');
        $this->rolesPath = $context->root.'/dependencies/ansible/roles';

        $helper = $context->getHelper('question');

        $finder = new Finder();
        $finder->directories()->depth(0)->in($this->rolesPath);
        $roles = array_map('basename', array_keys(iterator_to_array($finder->getIterator())));

        $roles = array_values(array_diff($roles, $provision[0]['roles']));
        $roles = array_combine(range(1, count($roles)), $roles);
        $roles[] = $context::SELECT_NONE;

        $text = <<<EOS
<info>
  Our ‘system’, ‘lamp’ and ‘node’ Ansible roles will be included by default.
  Please select any additional roles from which to include individual tasks.
  
  (Select multiple by entering a comma-delimited list, like “1,2,4”)
</info>
EOS;

        $question = new ChoiceQuestion($text, $roles);
        $question->setMultiselect(true);

        $roles = $helper->ask($context->input, $context->output, $question);

        if (!in_array($context::SELECT_NONE, $roles)) {
            $context->output->writeln('<comment>You have just selected: '.implode(', ', $roles).'</comment>');

            $basename = function ($file) {
                return basename($file, '.yaml');
            };

            foreach ($roles as $role) {
                $finder = new Finder();
                $tasks = $finder->files()->depth(0)->in("{$this->rolesPath}/{$role}/tasks")->name('*.yaml')->notName('main.yaml');

                $tasks = array_map($basename, array_keys(iterator_to_array($tasks->getIterator())));
                $tasks = array_combine(range(1, count($tasks)), $tasks);
                $tasks[] = $context::SELECT_NONE;

                $text = <<<EOS
<info>
  Which tasks do you want to include from the {$role} role?
  
  (Select multiple as before)
</info> 
EOS;

                $question = new ChoiceQuestion($text, $tasks);
                $question->setMultiselect(true);

                $tasks = $helper->ask($context->input, $context->output, $question);
                if (!in_array($context::SELECT_NONE, $tasks)) {
                    $context->output->writeln('You have just selected: ' . implode(', ', $tasks));
                    $provision[0]['tasks'] = array_merge(array_map(function ($task) use ($role) {
                        return ["include" => "{$this->anonymousRolesPath}/{$role}/tasks/{$task}.yaml"];
                    }, $tasks), $provision[0]['tasks']);
                }
            }
        }

        $provisionFile = $context->cwd.'/ansible/provision_vagrant.yaml';
        $context->filesystem->dumpFile($provisionFile, Yaml::dump($provision, 10, 2));
        $context->filesystem->appendToFile($provisionFile, PHP_EOL.<<< EOS
    # This is your provision file! Add any custom provisioning
    # tasks you want below.
EOS
            .PHP_EOL
        );
    }
}
