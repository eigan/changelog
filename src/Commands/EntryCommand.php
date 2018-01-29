<?php

namespace Logg\Commands;

use Logg\Entry\Entry;
use Logg\Filesystem;
use Logg\GitRepository;
use Logg\Handler\IEntryFileHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class EntryCommand extends Command
{
    /**
     * @var IEntryFileHandler
     */
    private $handler;

    /**
     * @var Filesystem
     */
    private $filesystem;
    
    /**
     * @var null|GitRepository
     */
    private $repository;
    
    public function __construct(
        IEntryFileHandler $handler,
        Filesystem $filesystem,
        GitRepository $repository = null
    ) {
        parent::__construct();

        $this->handler = $handler;
        $this->filesystem = $filesystem;
        $this->repository = $repository;
    }

    public function configure()
    {
        $this->setName('entry');
        $this->setDescription('Create log entry');

        $this->addArgument('title', InputArgument::OPTIONAL, 'Short description of what changed');
        $this->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'fix|new');
        $this->addOption('author', 'u', InputOption::VALUE_OPTIONAL);
        $this->addOption('name', 'f', InputOption::VALUE_OPTIONAL);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $title = $this->askForTitle($input, $io);
        $type = $this->askForType($input, $io);
        $author = $this->askForAuthor($input, $io);
        
        $name = $this->askForName($input, $io);
        
        try {
            $entry = new Entry($name, [
                'title' => $title,
                'type' => $type,
                'author' => $author
            ]);
        } catch (\InvalidArgumentException $e) {
            $output->writeln('Missing entry title');
            
            return 1;
        }

        $content = $this->handler->transform($entry);

        $io->writeln('');
        
        $io->note('Write: ' . $entry->getName(). '.' . $this->handler->getExtension());

        $io->write($content);

        $io->writeln('');
        if ($io->confirm('Is this ok?')) {
            $this->filesystem->writeEntry($entry);
        }
    }
    
    private function askForTitle(InputInterface $input, SymfonyStyle $output)
    {
        $title = $input->getArgument('title');
        
        if (empty($title) && $this->repository) {
            $title = $this->repository->getLastCommitMessage();
        }
        
        return $output->ask('Title', $title);
    }
    
    private function askForType(InputInterface $input, SymfonyStyle $output)
    {
        // TODO: Resolve from commit message
        $default = $input->getOption('type');
        
        $types = [
            '1' => 'new',
            '2' => 'fix',
            '3' => 'security',
            '0' => 'none'
        ];

        if (is_numeric($default) === false) {
            $default = array_search($default, $types, true);
        }
        
        $choice = new ChoiceQuestion(
            'Please specify the type of change',
            $types,
            $default
        );
        
        $choice->setValidator(function ($selected) {
            return $selected ?? '';
        });

        $type = $output->askQuestion($choice);

        if ($type === 'none') {
            $type = '';
        }
        
        if (is_numeric($type)) {
            return $types[$type];
        }
        
        return $type;
    }

    private function askForAuthor(InputInterface $input, SymfonyStyle $output)
    {
        $default = $input->getOption('author');
        
        if (empty($default) && $this->repository) {
            $default = $this->repository->getLastCommitAuthor();
        }
        
        return $output->ask('Author', $default);
    }

    private function askForName(InputInterface $input, SymfonyStyle $output)
    {
        $default = $input->getOption('name') ?? '';
        
        if (empty($default) && $this->repository) {
            $default = $this->repository->getCurrentBranchName();
        }
        
        $ask = function (string $default) use ($output) {
            return $output->ask('Save entry as:', $default, function ($typed) {
                if (strpos($typed, ' ') !== false) {
                    throw new \InvalidArgumentException('No spaces allowed');
                }

                return $typed;
            });
        };
        
        if (empty($default)) {
            $default = $ask($default);
        }
        
        while (file_exists($this->filesystem->getEntriesPath() . '/' . $default . '.' . $this->handler->getExtension())) {
            $output->note("Entry with name '$default' exists, please type other");
            
            $default = $this->uniqueNameSuggestion($default);
            $default = $ask($default);
        }
        
        return $default;
    }
    
    private function uniqueNameSuggestion(string $nameSuggestion)
    {
        $originalNameSuggestion = $nameSuggestion;
        $entriesPath = $this->filesystem->getEntriesPath();
        $ext = $this->handler->getExtension();
        
        $i = 1;
        while (file_exists($entriesPath . '/' . $nameSuggestion . '.' . $ext)) {
            $nameSuggestion = $originalNameSuggestion . '-' . $i;
            $i++;
        }
        
        return $nameSuggestion;
    }
}
