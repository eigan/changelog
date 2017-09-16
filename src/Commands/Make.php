<?php

namespace Logg\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Make extends Command
{
    protected function configure()
    {
        $this->setName('make');

        $this->addArgument('title', InputArgument::REQUIRED, 'Short description of what changed');
        $this->addOption('type', '', InputArgument::OPTIONAL, 'Fix|new');
        $this->addOption('author', '', InputArgument::OPTIONAL);
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        // ..
    }
}
