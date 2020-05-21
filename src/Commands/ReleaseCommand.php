<?php

namespace Logg\Commands;

use InvalidArgumentException;
use function filter_var;
use function json_encode;
use function json_last_error_msg;
use Logg\Entry\Entry;
use Logg\Entry\EntryCollector;
use Logg\Filesystem;
use Logg\Formatter\IFormatter;
use Logg\LogMerger;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use const FILTER_VALIDATE_BOOLEAN;

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

    protected function configure(): void
    {
        $this->setName('release');
        $this->setDescription('Parses the entries and append it to ' . $this->filesystem->getChangelogPath());

        $this->addArgument('headline', InputArgument::REQUIRED, 'The changelog headline');
        $this->addOption('minor', '', InputOption::VALUE_NONE, 'Set as minor release');
        $this->addOption('preview', '', InputOption::VALUE_NONE, 'Preview and exit');
        $this->addOption('preview-json', '', InputOption::VALUE_NONE, 'Preview with json and exit');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isJsonPreview = $input->getOption('preview-json');
        $isPreview = $input->getOption('preview') || $isJsonPreview;

        $io = new SymfonyStyle($input, $output);

        $entries = $this->collector->collect();
        
        if (empty($entries)) {
            if ($isPreview === false) {
                $output->writeln('No entries to append');
            }
            
            return 1;
        }

        $headline = $input->getArgument('headline');

        if (is_string($headline) === false) {
            throw new InvalidArgumentException('Invalid value for argument headline. Should be string.');
        }
        
        $isMinor = filter_var($input->getOption('minor'), FILTER_VALIDATE_BOOLEAN);
        
        $content = $this->formatter->format($headline, $entries, [
            'minor' => $isMinor
        ]);
        
        if ($isPreview === false) {
            $io->note('Append to: ' . $this->filesystem->getChangelogPath());
        }

        if($isJsonPreview) {
            $this->writeJsonContent($output, $headline, $entries);
            return 0;
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
        
        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param Entry[] $entries
     */
    private function writeJsonContent(OutputInterface $output, string $headline, array $entries): void
    {
        $data = [];
        
        foreach($entries as $entry) {
            $data[] = $entry->toArray();
        }
        
        $jsonFormatted = json_encode(['headline' => $headline, 'entries' => $data]);
        
        if($jsonFormatted === false) {
            throw new RuntimeException('Failed to create JSON string. Got error: ' . json_last_error_msg());
        }
        
        $output->write($jsonFormatted);
    }
}
