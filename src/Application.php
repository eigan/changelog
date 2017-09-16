<?php

namespace Logg;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Symfony\Component\Console\Application
{
    /**
     * @return InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        $definition = new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),

            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message'),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version'),
            new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output'),
            new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output'),
            new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question'),
        ]);

        $definition->addOption(new InputOption('--formatter', '', InputOption::VALUE_OPTIONAL, 'The entry formatter', 'markdown'));
        $definition->addOption(new InputOption('--file', '', InputOption::VALUE_OPTIONAL, 'The changelog file', 'CHANGELOG.md'));

        return $definition;
    }

    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        parent::configureIO($input, $output); // TODO: Change the autogenerated stub

        $argvInput = new ArgvInput();

        try {
            $argvInput->bind($this->getDefaultInputDefinition());
        } catch (RuntimeException $e) {
            // Symfony screams here since it cant handle the arguments sent to any of our commands
        }

        $formatter = $argvInput->getOption('formatter');

        switch ($formatter) {
            case 'markdown':
                // TODO: Set in container
                break;
        }
    }
}
