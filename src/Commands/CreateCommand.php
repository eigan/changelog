<?php

namespace Logg\Commands;

use Logg\Entry\Entry;
use Logg\Entry\EntryFileFactory;
use Logg\Filesystem;
use Logg\GitRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateCommand extends Command
{
    /**
     * @var EntryFileFactory
     */
    private $creator;

    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var GitRepository
     */
    private $repository;

    public function __construct(
        EntryFileFactory $entryCreator,
        Filesystem $filesystem,
        GitRepository $repository
    ) {
        parent::__construct();

        $this->creator = $entryCreator;
        $this->filesystem = $filesystem;
        $this->repository = $repository;
    }

    public function configure()
    {
        $this->setName('create');
        $this->setDescription('Create log entry');

        $this->addArgument('title', InputArgument::OPTIONAL, 'Short description of what changed');
        $this->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Fix|new');
        $this->addOption('author', 'u', InputOption::VALUE_OPTIONAL);
        $this->addOption('name', 'f', InputOption::VALUE_OPTIONAL);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getOption('type');
        $title = $input->getArgument('title') ?? $this->repository->getLastCommitMessage();
        $name = $input->getOption('name') ?? $this->repository->getCurrentBranchName();

        $io = new SymfonyStyle($input, $output);

        if (empty($type) || in_array($type, Entry::TYPES, true)) {
            $choice = new ChoiceQuestion(
                'Please specify the type of change',
                [
                    '1' => 'new',
                    '2' => 'fix',
                    '3' => 'security',
                    '4' => 'other'
                ]
            );

            $type = $io->askQuestion($choice);
        }

        $entry = new Entry($name, [
            'title' => $title,
            'type' => $type,
            'author' => $input->getOption('author') ?? ''
        ]);

        $entryFile = $this->creator->generate($entry);

        $io->writeln('');

        $io->note('Write: ' . $this->filesystem->getEntriesPath() . '/'. $entryFile->getFilename());
        $io->writeln('---');

        $io->write($entryFile->getContent());
        
        $io->writeln('');
        $io->askQuestion(new ConfirmationQuestion('Is this ok?'));

        $this->filesystem->writeEntry($entryFile);
    }
}
