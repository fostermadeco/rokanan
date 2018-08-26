<?php

namespace FosterMade\Rokanan\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;
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
            $output->write("<comment>• Checking that {$dependency} is installed</comment> ");

            $version = Yaml::parseFile("{$this->root}/dependencies/{$dependency}/version.yaml");

            $process = new Process($version['check']);
            $process->run();

            if ($process->getExitCode() === 0) {
                $output->write("<info>✔</info>", true);

                if ($version['version'] !== 0) {
                    $installed = trim($process->getOutput());

                    $output->writeln(str_repeat(' ', 2)."<comment>• Checking that the installed {$dependency} version is compatible</comment>");
                    $output->write(str_repeat(' ', 4) . "<info>{$installed} {$version['operator']} {$version['version']}</info> ");

                    if (true === version_compare($installed, $version['version'], $version['operator'])) {
                        $output->write("<info>✔</info>", true);
                    }
                }
            } else {

                $output->write("<info>✘</info>", true);

                $question = new Question("{$dependency} could not be found. Do you want to try to install it? (y/N)", "N");
                $install = $helper->ask($input, $output, $question);

                if (strtolower($install) === 'y') {
                    $process = new Process($version['install']);
                    $process->setPty(true);
                    $process->setTty(true);
                    $process->run(function ($type, $buffer) use ($output) {
                        $output->write($buffer, true);
                    });

                    if ($process->isSuccessful()) {
                        $output->writeln("<comment>{$dependency} was successfully installed.</comment>");
                        continue;
                    }

                    $output->writeln("<error>{$dependency} could not be installed.</error>");
                    exit(1);
                }
            }

            $tests = isset($version['additional_tests']) ? $version['additional_tests'] : [];

            foreach ($tests as $test) {
                $output->write(str_repeat(' ', 2)."<comment>• {$test['description']}</comment> ");
                $process = new Process($test['command']);
                $process->run();

                if ($process->isSuccessful()) {
                    $output->write("<info>✔</info>", true);
                    continue;
                }

                $fix = ($test['required']) ? 'y' : 'n';

                if (!$test['required']) {
                    $question = new Question(str_repeat(' ', 4) . '<comment>' . $test['message'] . " (y/N)</comment> ", "N");
                    $fix = $helper->ask($input, $output, $question);
                }

                if (strtolower($fix) === 'y') {
                    $process = new Process($test['correction']);
                    $process->setPty(true);
                    $process->setTty(true);
                    $process->run(function ($type, $buffer) use ($output) {
                        $output->write($buffer, true);
                    });

                    if ($process->isSuccessful()) {
                        $output->writeln(str_repeat(' ', 4)."<comment>{$test['success']}</comment>");
                        continue;
                    }
                } else {
                    $output->writeln(str_repeat(' ', 4) . "<comment>{$test['decline']}</comment>");
                }
            }
        }
    }
}
