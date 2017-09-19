<?php

namespace Logg\Commands;

use Logg\Entry\EntryCollector;
use Logg\Filesystem;
use Logg\Formatter\IFormatter;
use Logg\LogMerger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class Parse extends Command
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
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->collector = $collector;
        $this->merger = $logMerger;
        $this->formatter = $formatter;
    }

    protected function configure()
    {
        $this->setName('parse');
        $this->setDescription('Parses the entries and append it to CHANGELOG.md');

        $this->addArgument('headline', InputArgument::REQUIRED, 'The changelog headline');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $entries = $this->collector->collect();

        if (empty($entries)) {
            $output->writeln('No entries to append');

            return;
        }

        $content = $this->formatter->format($input->getArgument('headline'), $entries);

        $io->note('Append to: ' . $this->filesystem->getChangelogPath());

        $output->writeln('---');
        $output->write($content);
        $output->writeln('---');
        $output->writeln('');

        $continue = $io->askQuestion(new ConfirmationQuestion('Is this ok?'));

        if ($continue == false) {
            return;
        }
        
        $this->merger->append($input->getArgument('headline'), $entries);

        //$this->filesystem->cleanup();
    }
}
