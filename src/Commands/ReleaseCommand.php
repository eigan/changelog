<?php

namespace Logg\Commands;

use Logg\Entry\Entry;
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
        IFormatter $formatter,
        GitRepository $repository,
        IRemote $remote = null
    ) {
        $this->filesystem = $filesystem;
        $this->collector = $collector;
        $this->merger = $logMerger;
        $this->formatter = $formatter;
        $this->repository = $repository;
        $this->remote = $remote;
        
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

        $entries = $this->collector->collect();
        
        if ($since = $input->getOption('since')) {
            $entries = array_merge($entries, $this->findMergeEntries($since));
        }

        usort($entries, function ($firstEntry, $secondEntry) {
            $firstIndex = array_search($firstEntry->getType(), Entry::TYPES, true) ?? 10;
            $secondIndex = array_search($secondEntry->getType(), Entry::TYPES, true) ?? 10;

            return $firstIndex - $secondIndex;
        });
        
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

        $this->filesystem->cleanup();
    }
    
    private function findMergeEntries(string $since): array
    {
        $entries = [];
        
        // Go over each m
        $merges = $this->repository->getAllMerges($since);
    
        foreach ($merges as $merge) {
            $title = $this->extractTitle($merge);
            $type = $this->extractType($merge);
            $reference = $this->extractReference($merge);
            $author = $this->extractAuthor($merge);
            
            $name = str_replace(' ', '-', $title);
            
            $entry = new Entry($name, [
                'title' => $title,
                'type' => $type,
                'reference' => $reference,
                'author' => $author
            ]);
            
            if ($this->remote) {
                $this->remote->decorate($entry);
            }
            
            $entries[] = $entry;
        }
        
        return $entries;
    }
    
    private function extractTitle(array $message): ?string
    {
        if (isset($message[7])) {
            return trim($message[7]);
        }
        
        return null;
    }
    
    private function extractType(array $message): ?string
    {
        // TODO: Resolve gitlab merge request..
        return null;
    }
    
    private function extractReference(array $message): ?int
    {
        $combined = implode('', $message);

        preg_match_all('/\!(\d+)/', $combined, $matches, PREG_SET_ORDER, 0);
        
        if (isset($matches[0][1], $matches[0][1])) {
            return (int) $matches[0][1];
        }
        
        return null;
    }
    
    private function extractAuthor(array $message): ?string
    {
        $combined = implode("\n", $message);

        preg_match_all('/Author: (\w+)/', $combined, $matches, PREG_SET_ORDER, 0);
        
        if (isset($matches[0][1], $matches[0][1])) {
            return $matches[0][1];
        }

        return null;
    }
}
