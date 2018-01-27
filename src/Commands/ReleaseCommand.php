<?php

namespace Logg\Commands;

use Logg\Entry\EntryCollector;
use Logg\Filesystem;
use Logg\Formatter\IFormatter;
use Logg\LogMerger;
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
        $this->addOption('minor', '', InputOption::VALUE_NONE, 'Set as minor release');
        $this->addOption('preview', '', InputOption::VALUE_NONE, 'Preview and exit');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isPreview = $input->getOption('preview');
        
        $io = new SymfonyStyle($input, $output);

        $entries = $this->collector->collect();
        
        if (empty($entries)) {
            if ($isPreview === false) {
                $output->writeln('No entries to append');
            }
            
            exit(1);
        }

        $content = $this->formatter->format($input->getArgument('headline'), $entries, [
            'minor' => $input->getOption('minor')
        ]);
        
        if ($isPreview === false) {
            $io->note('Append to: ' . $this->filesystem->getChangelogPath());
        }

        $output->write($content);
        if ($isPreview === false) {
            $output->writeln('');
        }
        
        $continue = false;
        
        if ($isPreview === false) {
            $continue = $io->askQuestion(new ConfirmationQuestion('Is this ok?'));
        }

        if ($continue === false) {
            return 0;
        }
        
        $this->merger->append($content);

        $this->filesystem->cleanup();
    }
}
