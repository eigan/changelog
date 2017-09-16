<?php

namespace Logg\Commands;

use Logg\Entry\EntryCollector;
use Logg\LogMerger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Parse extends Command
{
    protected function configure()
    {
        $this->setName('parse');
        $this->setDescription('Parses the entries and append it to CHANGELOG.md');

        $this->addArgument('headline', InputArgument::REQUIRED, 'The changelog headline');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collector = new EntryCollector();
        $entries = $collector->collect();

        $merger = new LogMerger();
        $merger->append($input->getArgument('headline'), $entries);

        // TODO: Remove entry files
    }
}
