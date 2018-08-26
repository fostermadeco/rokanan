<?php

namespace FosterMade\Rokanan\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

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
    }
}
