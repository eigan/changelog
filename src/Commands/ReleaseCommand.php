<?php

namespace Logg\Commands;

use Logg\Entry\EntryCollector;
use Logg\Filesystem;
use Logg\Formatter\IFormatter;
use Logg\GitRepository;
use Logg\LogMerger;
use Logg\Remotes\IRemote;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReleaseCommand extends Command
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var EntryCollector
     */
    private $collector;

    /**
     * @var LogMerger
     */
    private $merger;

    /**
     * @var IFormatter
     */
    private $formatter;

    /**
     * @var GitRepository
     */
    private $repository;
    
    /**
     * @var IRemote
     */
    private $remote;
    
    public function __construct(
        Filesystem $filesystem,
        EntryCollector $collector,
        LogMerger $logMerger,
        IFormatter $formatter
    ) {
        $this->filesystem = $filesystem;
        $this->collector = $collector;
        $this->merger = $logMerger;
        $this->formatter = $formatter;
        
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('release');
        $this->setDescription('Parses the entries and append it to ' . $this->filesystem->getChangelogPath());

        $this->addArgument('headline', InputArgument::REQUIRED, 'The changelog headline');
        $this->addOption('since', '', InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $entries = $this->collector->collect($input->getOption('since'));
        
        if (empty($entries)) {
            $output->writeln('No entries to append');

            exit(1);
        }

        $content = $this->formatter->format($input->getArgument('headline'), $entries);

        $io->note('Append to: ' . $this->filesystem->getChangelogPath());

        $output->writeln('---');
        $output->write($content);
        $output->writeln('---');
        $output->writeln('');

        $continue = $io->askQuestion(new ConfirmationQuestion('Is this ok?'));

        if ($continue == false) {
            exit(1);
        }
        
        $this->merger->append($input->getArgument('headline'), $entries);

        $this->filesystem->cleanup();
    }
}
