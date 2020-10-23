<?php

namespace FosterMade\Rokanan\Task;

use FosterMade\Rokanan\Command\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class BuildGroupVarsTask
{
    /**
     * @var Command
     */
    protected $context;

    /**
     * @var array
     */
    protected $vars = [];

    public function __invoke(Command $context)
    {
        $this->context = $context;

        $this->buildGroupVarsFile();
    }

    protected function buildGroupVarsFile()
    {
        $vars = Yaml::parseFile($this->context->root.'/dependencies/ansible/group_vars.yaml');

        $helper = $this->context->getHelper('question');

        foreach ($vars as $var) {
            $label = $var['question'].':'.(isset($var['default']) ? " ({$var['default']})" : '').' ';

            $label = "<comment>$label</comment>";
            $default = isset($var['default']) ? $var['default'] : null;

            $question = (!isset($var['choices'])) ? new Question($label, $default) : new ChoiceQuestion($label, $var['choices'], $default);

            $this->vars[$var['name']] = $helper->ask($this->context->input, $this->context->output, $question);
        }

        $this->vars['document_root'] = "/var/www/html/{{ public_folder }}";
        $this->vars['php_modules'] = [];

        $this->context->output->writeln(<<<EOS
<info>
Most PHP packages are now included by default. If you find you still need an
additional package, you can add it manually to the "php_modules" array in
"ansible/group_vars/all".
</info>
EOS
        );

        $hostVarsFile = $this->context->cwd.'/ansible/group_vars/all';
        $this->context->filesystem->dumpFile($hostVarsFile, Yaml::dump($this->vars, 10, 2, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE));
    }
}
