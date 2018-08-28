<?php

namespace FosterMade\Rokanan\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

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
    protected $path;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument('path', InputArgument::OPTIONAL, 'The path to the project to initialize', '.');
        $this->filesystem = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->root = realpath(__DIR__.'/../..');
        $this->path = realpath($input->getArgument('path'));

        $this->input = $input;
        $this->output = $output;
    }

    /**
     * @param string $command
     * @param bool $pty
     * @param bool $tty
     * @return Process
     */
    protected function createProcess($command, $pty = true, $tty = true)
    {
        $process = new Process($command);
        $process->setPty($pty);
        $process->setTty($tty);

        return $process;
    }

    /**
     * @param Process $process
     * @param bool $display
     */
    protected function runProcess(Process $process, $display = true)
    {
        false === $display ? $process->run() : $process->run(function ($type, $buffer) {
            $this->output->write($buffer, true);
        });
    }
}
