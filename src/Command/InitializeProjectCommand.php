<?php

namespace FosterMade\Rokanan\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class InitializeProjectCommand extends Command
{
    /**
     * @var string
     */
    protected $rolesPath;

    /**
     * @var string
     */
    protected $anonymousRolesPath;

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('initialize')
            ->setAliases(['init'])
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

        if (!in_array($this->getVagrantStatus(), ['', 'not created'])) {
            throw new \Exception(
                'An existing Vagrant environment has been detected in '.$this->cwd.'.'.PHP_EOL.
                'Please destroy it before initializing it as a Rokanan project.'
            );
        }

        $this->buildProvisionFile();
        $this->createAnsibleConfigFile();
        $this->createVagrantFile();
        $this->createRokananLockFile();
    }

    /**
     *
     */
    protected function buildProvisionFile()
    {
        $this->output->writeln('<comment>Let’s build a provision file . . .</comment>');

        $provision = Yaml::parseFile($this->root.'/dependencies/ansible/provision.yaml');
        $this->rolesPath = $this->root.'/dependencies/ansible/roles';
        $this->anonymousRolesPath = $this->anonymousRoot.'/dependencies/ansible/roles';
        $provision[0]['tasks'][1]['template']['src'] = $this->anonymousRolesPath."/{$provision[0]['tasks'][1]['template']['src']}";

        $helper = $this->getHelper('question');

        $finder = new Finder();
        $finder->directories()->depth(0)->in($this->rolesPath);
        $roles = array_map('basename', array_keys(iterator_to_array($finder->getIterator())));

        $roles = array_values(array_diff($roles, $provision[0]['roles']));
        $roles = array_combine(range(1, count($roles)), $roles);
        $roles[] = static::SELECT_NONE;

        $text = <<<EOS
<info>
  Our ‘system’, ‘lamp’ and ‘node’ Ansible roles will be included by default.
  Please select any additional roles from which to include individual tasks.
  
  (Select multiple by entering a comma-delimited list, like “1,2,4”)
</info>
EOS;

        $question = new ChoiceQuestion($text, $roles);
        $question->setMultiselect(true);

        $roles = $helper->ask($this->input, $this->output, $question);

        if (!in_array(static::SELECT_NONE, $roles)) {
            $this->output->writeln('<comment>You have just selected: '.implode(', ', $roles).'</comment>');

            $basename = function ($file) {
                return basename($file, '.yaml');
            };

            foreach ($roles as $role) {
                $finder = new Finder();
                $tasks = $finder->files()->depth(0)->in("{$this->rolesPath}/{$role}/tasks")->name('*.yaml')->notName('main.yaml');

                $tasks = array_map($basename, array_keys(iterator_to_array($tasks->getIterator())));
                $tasks = array_combine(range(1, count($tasks)), $tasks);
                $tasks[] = static::SELECT_NONE;

                $text = <<<EOS
<info>
  Which tasks do you want to include from the {$role} role?
  
  (Select multiple as before)
</info> 
EOS;

                $question = new ChoiceQuestion($text, $tasks);
                $question->setMultiselect(true);

                $tasks = $helper->ask($this->input, $this->output, $question);
                if (!in_array(static::SELECT_NONE, $tasks)) {
                    $this->output->writeln('You have just selected: ' . implode(', ', $tasks));
                    $provision[0]['tasks'] = array_merge(array_map(function ($task) use ($role) {
                        return ["include" => "{$this->anonymousRolesPath}/{$role}/tasks/{$task}.yaml"];
                    }, $tasks), $provision[0]['tasks']);
                }
            }
        }

        $provisionFile = $this->cwd.'/ansible/provision_dev.yaml';
        $this->filesystem->dumpFile($provisionFile, Yaml::dump($provision, 10, 2));
        $this->filesystem->appendToFile($provisionFile, PHP_EOL.<<< EOS
    # This is your provision file! Add any custom provisioning
    # tasks you want below.
EOS
            .PHP_EOL
        );
    }

    /**
     *
     */
    protected function createAnsibleConfigFile()
    {
        $config = Yaml::parseFile($this->root.'/dependencies/ansible/config.yaml');
        $config['defaults']['roles_path'] = ':${COMPOSER_HOME}/vendor/fostermadeco/rokanan/dependencies/ansible/roles:~/.composer/vendor/fostermadeco/rokanan/dependencies/ansible/roles:/etc/ansible/roles:';
        $configFile = $this->cwd.'/ansible.cfg';

        foreach ($config as $section => $values) {
            $this->filesystem->appendToFile($configFile, "[{$section}]".PHP_EOL);

            foreach ($values as $key => $value) {
                $this->filesystem->appendToFile($configFile, "{$key} = {$value}".PHP_EOL);
            }
        }
    }

    /**
     *
     */
    protected function createVagrantFile()
    {
        $this->filesystem->copy($this->root.'/dependencies/vagrant/Vagrantfile', $this->cwd.'/Vagrantfile');
    }

    /**
     *
     */
    protected function createRokananLockFile()
    {
        $lockFile = $this->cwd.'/rokanan.lock';
        $this->filesystem->dumpFile($lockFile, $this->getGitHead().PHP_EOL);
    }

    /**
     * @return string
     */
    protected function getVagrantStatus()
    {
        $process = new Process('vagrant status --machine-readable | awk -F, \'/state-human-short/ { print $NF }\'', $this->cwd);
        $process->run();

        return trim($process->getOutput());
    }

    /**
     * @return string
     */
    protected function getGitHead()
    {
        $process = new Process('git rev-parse HEAD', $this->root);
        $process->mustRun();

        return trim($process->getOutput());
    }
}
