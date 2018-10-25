<?php

namespace FosterMade\Rokanan\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class Command extends BaseCommand
{
    /**
     * @var string
     */
    const SELECT_NONE = 'Select none';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $root;

    /**
     * @var string
     */
    protected $cwd;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    protected $projectRoot;

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->addOption('working-dir', 'd', InputArgument::OPTIONAL, 'If specified, use the given directory as working directory.', '.');
        $this->filesystem = new Filesystem();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->root = realpath(__DIR__.'/../..');
        $this->cwd = realpath($input->getOption('working-dir'));

        $this->input = $input;
        $this->output = $output;

        $localVars = $this->cwd.'/ansible/host_vars/local';
        if ($this->filesystem->exists($localVars)) {
            $vars = Yaml::parseFile($localVars);
            $this->projectRoot = '/var/www/'.$vars['hostname'];
        }
    }

    /**
     * @param string|array $command
     * @param bool $pty
     * @param bool $tty
     * @param null $cwd
     * @return Process
     */
    protected function createProcess($command, $pty = true, $tty = true, $cwd = null)
    {
        $process = new Process($command, $cwd ?: $this->cwd);
        $process->setPty($pty);
        $process->setTty($tty);

        return $process;
    }

    /**
     * @param string|array $command
     * @return Process
     */
    protected function createVagrantProcess($command)
    {
        $process = $this->createProcess($command);
        $process->inheritEnvironmentVariables();
        $process->setEnv(['VAGRANT_USE_VAGRANT_TRIGGERS' => 1]);

        return $process;
    }
}
