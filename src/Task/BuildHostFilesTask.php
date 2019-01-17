<?php

namespace FosterMade\Rokanan\Task;

use FosterMade\Rokanan\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class BuildHostFilesTask
{
    /**
     * @var Command
     */
    protected $context;

    /**
     * @var array
     */
    protected $vars = [];

    public function runInContext(Command $context)
    {
        $this->context = $context;

        $this->buildHostVarsFile();
        $this->buildHostsFile();

        $context->vars += $this->vars;
    }

    protected function buildHostVarsFile()
    {
        $vars = Yaml::parseFile($this->context->root.'/dependencies/ansible/host_vars.yaml');

        $helper = $this->context->getHelper('question');

        foreach ($vars as $idx => $var) {
            foreach (array_slice($vars, 0, $idx) as $prev) {
                if (isset($var['default']) && strpos($var['default'], "{{ {$prev['name']} }}") !== false) {
                    $var['default'] = str_replace("{{ {$prev['name']} }}", $this->vars[$prev['name']], $var['default']);
                    break;
                }
            }

            $label = $var['question'].':'.(isset($var['default']) ? " ({$var['default']})" : '').' ';
            $question = new Question("<comment>$label</comment>", isset($var['default']) ? $var['default'] : null);
            $this->vars[$var['name']] = $helper->ask($this->context->input, $this->context->output, $question);
        }

        $hostVarsFile = $this->context->cwd.'/ansible/host_vars/local';
        $this->context->filesystem->dumpFile($hostVarsFile, Yaml::dump($this->vars, 10, 2));
    }

    protected function buildHostsFile()
    {
        $hosts = Yaml::parseFile($this->context->root.'/dependencies/ansible/hosts');
        $hosts['all']['hosts']['local']['ansible_host'] = $this->vars['hostname'];
        $hostsFile = $this->context->cwd.'/ansible/hosts';
        $this->context->filesystem->dumpFile($hostsFile, Yaml::dump($hosts, 10, 2));
    }
}
