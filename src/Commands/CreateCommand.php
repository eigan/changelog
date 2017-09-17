<?php

namespace Logg\Commands;

use Logg\Entry\Entry;
use Logg\Entry\EntryCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class CreateCommand extends Command
{
    /**
     * @var EntryCreator
     */
    private $creator;

    public function __construct(EntryCreator $entryCreator)
    {
        parent::__construct();

        $this->creator = $entryCreator;
    }

    public function configure()
    {
        $this->setName('create');
        $this->setDescription('Create log entry');

        $this->addArgument('title', InputArgument::REQUIRED, 'Short description of what changed');
        $this->addOption('type', '', InputArgument::OPTIONAL, 'Fix|new');
        $this->addOption('author', '', InputArgument::OPTIONAL);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getOption('type');

        if (empty($type) || in_array($type, Entry::TYPES, true)) {
            $helper = new QuestionHelper();
            $choice = new ChoiceQuestion(
                'Please specify the type of change',
                [
                    '1' => 'feature',
                    '2' => 'fix',
                    '3' => 'security',
                    '4' => 'other'
                ]
            );

            $type = $helper->ask($input, $output, $choice);
        }

        $entry = new Entry($input->getArgument('title'), [
            'title' => $input->getArgument('title'),
            'type' => $type,
            'author' => $input->getOption('author')
        ]);

        $this->creator->generate($entry);
    }
}
