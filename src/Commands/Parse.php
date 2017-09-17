<?php

namespace Logg\Commands;

use Logg\Entry\EntryCollector;
use Logg\Filesystem;
use Logg\LogMerger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function __construct(
        Filesystem $filesystem,
        EntryCollector $collector,
        LogMerger $logMerger
    ) {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->collector = $collector;
        $this->merger = $logMerger;
    }

    protected function configure()
    {
        $this->setName('parse');
        $this->setDescription('Parses the entries and append it to CHANGELOG.md');

        $this->addArgument('headline', InputArgument::REQUIRED, 'The changelog headline');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entries = $this->collector->collect();

        $this->merger->append($input->getArgument('headline'), $entries);

        $this->filesystem->cleanup();
    }
}
