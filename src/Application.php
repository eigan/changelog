<?php

namespace Logg;

use Logg\Commands\EntryCommand;
use Logg\Commands\ReleaseCommand;
use Logg\Entry\EntryCollector;
use Logg\Entry\IEntryReferenceProvider;
use Logg\Entry\References\GitlabReferenceProvider;
use Logg\Formatter\IFormatter;
use Logg\Formatter\MarkdownFormatter;
use Logg\Handler\IEntryFileHandler;
use Logg\Handler\YamlHandler;
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
     * @var string
     */
    private $rootPath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IFormatter
     */
    private $formatter;

    /**
     * @var GitRepository
     */
    private $repository;

    /**
     * @var IEntryFileHandler
     */
    private $handler;

    /**
     * @var IEntryReferenceProvider
     */
    private $referenceProvider;

    public function __construct(string $rootPath)
    {
        parent::__construct('Log generator', 'dev');

        $this->rootPath = $rootPath;
    }

    /**
     * @return InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        $definition = new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),

            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version'),
            new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output'),
        ]);

        $definition->addOption(new InputOption('--formatter', '', InputOption::VALUE_OPTIONAL, 'The entry formatter', 'markdown'));
        $definition->addOption(new InputOption('--file', '', InputOption::VALUE_OPTIONAL, 'The changelog file', 'CHANGELOG.md'));
        $definition->addOption(new InputOption('--entries', '', InputOption::VALUE_OPTIONAL, 'The changelogs path', './changelogs'));

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

        $this->referenceProvider = new GitlabReferenceProvider();

        $formatter = $argvInput->getOption('formatter');

        switch ($formatter) {
            case 'markdown':
                $this->formatter = new MarkdownFormatter($this->referenceProvider);
                break;
        }

        $entriesPath = $argvInput->getOption('entries');
        $changelogPath = $argvInput->getOption('file');

        $git = new GitRepository($this->rootPath);

        $didSetup = $this->setupEnvironment($changelogPath, $entriesPath, $output);

        if ($didSetup === false) {
            exit();
        }

        $this->repository = $git;
        $this->handler = new YamlHandler();

        $this->filesystem = new Filesystem($changelogPath, $entriesPath, $this->handler);
    }

    protected function setupEnvironment(string $changelogPath, string $entriesPath, OutputInterface $output)
    {
        if (file_exists($changelogPath) === false) {
            $output->writeln('Could not find ' . $changelogPath);
            return false;
        }

        if (is_dir($entriesPath) === false) {
            $output->writeln('Could not find entries path: ' . $entriesPath);
            return false;
        }

        return true;
    }

    protected function getDefaultCommands()
    {
        $parent =  parent::getDefaultCommands(); // TODO: Change the autogenerated stub

        $own = [
            new EntryCommand($this->handler, $this->filesystem, $this->repository, $this->referenceProvider),
            new ReleaseCommand(
                $this->filesystem,
                new EntryCollector($this->filesystem, $this->handler),
                new LogMerger($this->filesystem, $this->formatter),
                $this->formatter
            )
        ];

        return array_merge($parent, $own);
    }
}
