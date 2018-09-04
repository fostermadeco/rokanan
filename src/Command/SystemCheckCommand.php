<?php

namespace FosterMade\Rokanan\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class SystemCheckCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('system-check')
            ->setAliases(['check'])
            ->setDescription('Checks the system for all prerequisites with an option to fix missing dependencies')
            ->setHelp(
                <<<EOS
The <info>system-check</info> command checks your system for all
Rokanan prerequisites and will ask you if you want to install any
missing dependencies.

<info>rokanan system-check</info>

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

        $dependencies = Yaml::parseFile($this->root.'/dependencies/list.yaml');

        $helper = $this->getHelper('question');

        foreach ($dependencies as $dependency) {
            $this->output->write("<comment>• Checking that {$dependency} is installed</comment> ");

            $version = Yaml::parseFile("{$this->root}/dependencies/{$dependency}/version.yaml");

            $process = $this->createProcess($version['check'], false, false);
            $this->runProcess($process, false);

            if ($process->getExitCode() === 0) {
                $this->output->write("<info>✔</info>", true);

                if ($version['version'] !== 0) {
                    $installed = trim($process->getOutput());

                    $this->output->writeln(str_repeat(' ', 2)."<comment>• Checking that the installed {$dependency} version is compatible</comment>");
                    $this->output->write(str_repeat(' ', 4) . "<info>{$installed} {$version['operator']} {$version['version']}</info> ");

                    if (true === version_compare($installed, $version['version'], $version['operator'])) {
                        $this->output->write("<info>✔</info>", true);
                    } else {
                        $this->output->write("<info>✘</info>", true);
                    }
                }
            } else {

                $this->output->write("<info>✘</info>", true);

                $question = new Question("{$dependency} could not be found. Do you want to try to install it? (y/N)", "N");
                $install = $helper->ask($this->input, $this->output, $question);

                if (strtolower($install) === 'y') {
                    $process = $this->createProcess($version['install'], true, true);
                    $this->runProcess($process);

                    if ($process->isSuccessful()) {
                        $this->output->writeln("<comment>{$dependency} was successfully installed.</comment>");
                        continue;
                    }

                    $this->output->writeln("<error>{$dependency} could not be installed.</error>");
                    exit(1);
                }
            }

            $tests = isset($version['additional_tests']) ? $version['additional_tests'] : [];

            foreach ($tests as $test) {
                $this->output->write(str_repeat(' ', 2)."<comment>• {$test['description']}</comment> ");
                $process = $this->createProcess($test['command'], false, false);
                $this->runProcess($process, false);

                if ($process->isSuccessful()) {
                    $this->output->write("<info>✔</info>", true);
                    continue;
                }

                $this->output->write("<info>✘</info>", true);

                $fix = ($test['required']) ? 'y' : 'n';

                if ($test['required']) {
                    $this->output->writeln(str_repeat(' ', 4)."<comment>{$test['message']}</comment>");
                } else {
                    $question = new Question(str_repeat(' ', 4).'<comment>' . $test['message'] . " (y/N)</comment> ", "N");
                    $fix = $helper->ask($this->input, $this->output, $question);
                }

                if (strtolower($fix) === 'y') {
                    $process = $this->createProcess($test['correction']);
                    $this->runProcess($process);

                    if ($process->isSuccessful()) {
                        $this->output->writeln(str_repeat(' ', 4)."<comment>{$test['success']}</comment>");

                        if (isset($test['action'])) {
                            $this->output->writeln(str_repeat(' ', 4)."<comment>{$test['action']}</comment>");
                        }

                        continue;
                    }
                } else {
                    $this->output->writeln(str_repeat(' ', 4) . "<comment>{$test['decline']}</comment>");
                }
            }
        }
    }
}
