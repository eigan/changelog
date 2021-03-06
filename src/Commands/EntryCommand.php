<?php

declare(strict_types=1);

namespace Logg\Commands;

use InvalidArgumentException;
use Logg\Entry\Entry;
use Logg\Filesystem;
use Logg\Formatter\IFormatter;
use Logg\GitRepository;
use Logg\Handler\IEntryFileHandler;
use function substr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class EntryCommand extends Command
{
    /**
     * @var IEntryFileHandler
     */
    private $handler;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var GitRepository|null
     */
    private $repository;

    /**
     * @var IFormatter
     */
    private $changelogFormatter;

    public function __construct(
        IEntryFileHandler $handler,
        Filesystem $filesystem,
        IFormatter $formatter,
        GitRepository $repository = null
    ) {
        parent::__construct();

        $this->handler = $handler;
        $this->filesystem = $filesystem;
        $this->changelogFormatter = $formatter;
        $this->repository = $repository;
    }

    public function configure(): void
    {
        $this->setName('entry');
        $this->setDescription('Create log entry');

        $this->addArgument('title', InputArgument::OPTIONAL, 'Short description of what changed');
        $this->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'fix|new');
        $this->addOption('author', 'u', InputOption::VALUE_OPTIONAL);
        $this->addOption('name', 'f', InputOption::VALUE_OPTIONAL);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $title = $this->askForTitle($input, $io);
        $type = $this->askForType($input, $io);
        $author = $this->askForAuthor($input, $io);

        $name = $this->askForName($input, $title, $io);

        try {
            $entry = new Entry($name, [
                'title' => $title,
                'type' => $type,
                'author' => $author,
            ]);
        } catch (InvalidArgumentException $e) {
            $output->writeln('Missing entry title');

            return 1;
        }

        $content = $this->handler->transform($entry);

        $io->writeln('');

        $io->note('Wrote to: '.$entry->getName().'.'.$this->handler->getExtension());

        $io->write($content);

        $io->writeln('');

        $this->filesystem->writeEntry($entry);

        return 0;
    }

    private function askForTitle(InputInterface $input, SymfonyStyle $output): string
    {
        $title = $input->getArgument('title');

        if (\is_array($title)) {
            throw new InvalidArgumentException('Only one title is allowed');
        }

        if (empty($title) && $this->repository) {
            $title = $this->repository->getLastCommitMessage();
        }

        return $output->ask('Title', $title);
    }

    private function askForType(InputInterface $input, SymfonyStyle $output): string
    {
        // TODO: Resolve from commit message
        $default = $input->getOption('type');

        if (null !== $default && false === \is_string($default)) {
            throw new InvalidArgumentException('Invalid value for argument type. Should be string.');
        }

        $suggestedTypes = $this->changelogFormatter->getSuggestedTypes();
        $types = $this->getFormattedSuggestedTypes();

        if ($default && false === is_numeric($default)) {
            $default = $this->resolveDefaultTypeIndex($default);
        }

        $choice = new ChoiceQuestion(
            'Please specify the type of change',
            $types,
            $default
        );

        $choice->setValidator(static function ($selected) {
            return $selected ?? '';
        });

        $type = $output->askQuestion($choice);

        if ('none' === $type) {
            $type = '';
        }

        if (\is_int($type) && isset($suggestedTypes[$type - 1])) {
            return $suggestedTypes[$type - 1]->key;
        }

        foreach ($suggestedTypes as $index => $suggestedType) {
            if (is_numeric($type) && $index + 1 === (int) $type) {
                // We never start from index 0, so we add 1 to index here
                return $suggestedType->key;
            }

            if (false !== strpos($type, $suggestedType->label)) {
                return $suggestedType->key;
            }
        }

        return $type;
    }

    private function askForAuthor(InputInterface $input, SymfonyStyle $output): string
    {
        $default = $input->getOption('author');

        if (null !== $default && false === \is_string($default)) {
            throw new InvalidArgumentException('Invalid value for argument author. Should be string.');
        }

        if (empty($default) && $this->repository) {
            $default = $this->repository->getLastCommitAuthor();
        }

        return $output->ask('Author', $default);
    }

    private function askForName(InputInterface $input, string $title, SymfonyStyle $output): string
    {
        $default = $input->getOption('name') ?? '';

        if (!\is_string($default)) {
            throw new InvalidArgumentException('Only one name is allowed');
        }

        if (empty($default) && $title) {
            $default = $this->fileNameFromTitle($title);
        }

        if (empty($default) && $this->repository) {
            $default = $this->repository->getCurrentBranchName();
        }

        $ask = static function (string $default) use ($output): string {
            return $output->ask('Save entry as:', $default, static function (string $typed) {
                if (false !== strpos($typed, ' ')) {
                    throw new InvalidArgumentException('No spaces allowed');
                }

                return $typed;
            });
        };

        if (empty($default)) {
            $default = $ask('');
        }

        while (file_exists($this->filesystem->getEntriesPath().'/'.$default.'.'.$this->handler->getExtension())) {
            $output->note("Entry with name '{$default}' exists, please type other");

            $default = $this->uniqueNameSuggestion($default);
            $default = $ask($default);
        }

        return $default;
    }

    /**
     * @return array<int, string>
     */
    private function getFormattedSuggestedTypes(): array
    {
        $suggestions = $this->changelogFormatter->getSuggestedTypes();
        $types = [];

        $i = 1;
        foreach ($suggestions as $key => $suggestion) {
            $description = $suggestion->description;

            if ($description) {
                $description = ' <comment>('.$suggestion->description.')</>';
            }

            $types[$i++] = '<options=bold>'.$suggestion->label.'</>'.$description;
        }

        return $types;
    }

    private function resolveDefaultTypeIndex(string $type): int
    {
        foreach ($this->changelogFormatter->getSuggestedTypes() as $index => $suggestedType) {
            if ($type === $suggestedType->key) {
                // We always present options from 1, so increment here
                return $index + 1;
            }
        }

        return \count($this->changelogFormatter->getSuggestedTypes());
    }

    private function uniqueNameSuggestion(string $nameSuggestion): string
    {
        $originalNameSuggestion = $nameSuggestion;
        $entriesPath = $this->filesystem->getEntriesPath();
        $ext = $this->handler->getExtension();

        $i = 1;
        while (file_exists($entriesPath.'/'.$nameSuggestion.'.'.$ext)) {
            $nameSuggestion = $originalNameSuggestion.'-'.$i;
            ++$i;
        }

        return $nameSuggestion;
    }

    private function fileNameFromTitle(string $title, string $separator = '-'): ?string
    {
        // Convert all dashes/underscores into separator
        $flip = '-' === $separator ? '_' : '-';

        $title = preg_replace('!['.preg_quote($flip).']+!u', $separator, $title);

        if (false === \is_string($title)) {
            return null;
        }

        // Replace @ with the word 'at'
        $title = str_replace('@', $separator.'at'.$separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', mb_strtolower($title));

        if (false === \is_string($title)) {
            return null;
        }

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);

        if (false === \is_string($title)) {
            return null;
        }

        return substr(trim($title, $separator), 0, 90);
    }
}
