<?php

namespace Logg\Commands;

use Logg\Entry\Entry;
use Logg\Filesystem;
use Logg\GitRepository;
use Logg\Handler\IEntryFileHandler;
use Logg\Remotes\IRemote;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\OutputStyle;
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
     * @var GitRepository
     */
    private $repository;

    /**
     * @var ?IRemote
     */
    private $remote;
    
    public function __construct(
        IEntryFileHandler $handler,
        Filesystem $filesystem,
        GitRepository $repository,
        IRemote $remote = null
    ) {
        parent::__construct();

        $this->handler = $handler;
        $this->filesystem = $filesystem;
        $this->repository = $repository;
        $this->remote = $remote;
    }

    public function configure()
    {
        $this->setName('entry');
        $this->setDescription('Create log entry');

        $this->addArgument('title', InputArgument::OPTIONAL, 'Short description of what changed');
        $this->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Fix|new');
        $this->addOption('author', 'u', InputOption::VALUE_OPTIONAL);
        $this->addOption('name', 'f', InputOption::VALUE_OPTIONAL);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $title = $this->askForTitle($input, $io);
        $type = $this->askForType($input, $io);
        $author = $this->askForAuthor($input, $io);
        $reference = '';
        
        if ($this->remote) {
            $reference = $this->askForReference($input, $io);
        }
        $name = $this->askForName($input, $io);
        
        $entry = new Entry($name, [
            'title' => $title,
            'type' => $type,
            'author' => $author,
            'reference' => $reference
        ]);

        $content = $this->handler->transform($entry);

        $io->writeln('');

        $io->note('Write: ' . $this->filesystem->getEntriesPath() . '/'. $entry->getName(). '.' . $this->handler->getExtension());

        $io->write($content);

        $io->writeln('');
        if ($io->confirm('Is this ok?')) {
            $this->filesystem->writeEntry($entry);
        }
    }
    
    private function askForTitle(InputInterface $input, OutputStyle $output)
    {
        $title = $input->getArgument('title') ?? $this->repository->getLastCommitMessage();
        
        return $output->ask('Title', $title);
    }
    
    private function askForType(InputInterface $input, OutputStyle $output)
    {
        // TODO: Resolve from commit message
        $default = $input->getOption('type');
        
        $types = [
            '1' => 'new',
            '2' => 'fix',
            '3' => 'security',
            '0' => 'none'
        ];
        
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

    private function askForAuthor(InputInterface $input, OutputStyle $output)
    {
        $default = $input->getOption('author') ?? $this->repository->getLastCommitAuthor();

        return $output->ask('Author', $default);
    }

    private function askForName(InputInterface $input, OutputStyle $output)
    {
        $default = $input->getOption('name') ?? $this->repository->getCurrentBranchName();
        
        while (file_exists($this->filesystem->getEntriesPath() . '/' . $default . '.' . $this->handler->getExtension())) {
            $output->note("Entry with name '$default' exists, please type other");
            $default = $output->ask('Save in changelogs/ as', $default, function ($typed) {
                if (strpos($typed, ' ') !== false) {
                    throw new \InvalidArgumentException('No spaces allowed');
                }
                
                return $typed;
            });
        }
        
        return $default;
    }
    
    private function askForReference(InputInterface $input, OutputStyle $output)
    {
        return $this->remote->askForReference($output, '');
    }
}
