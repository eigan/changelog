<?php

namespace Logg\Entry;

use Logg\GitRepository;
use Logg\Handler\IEntryFileHandler;

/**
 * Creates the entry file
 *
 * @package Logg\Entry
 */
class EntryFileFactory
{
    /**
     * @var IEntryFileHandler
     */
    private $handler;

    /**
     * @var GitWorkingCopy
     */
    private $git;

    public function __construct(
        IEntryFileHandler $handler,
        GitRepository $git
    ) {
        $this->handler = $handler;
        $this->git = $git;
    }

    /**
     * @param Entry $entry
     *
     * @return EntryFile
     */
    public function generate(Entry $entry): EntryFile
    {
        $fileContent = $this->handler->transform($entry);

        return new EntryFile($entry->getName(), $fileContent);
    }

    /**
     * Try to make a filename out of the title
     *
     * @param Entry $entry
     *
     * @return string
     */
    private function makeFilename(Entry $entry): string
    {
        $title = $this->git->getCurrentBranchName() ?? $entry->getTitle();

        return str_replace([' '], ['-'], $title) . '.yml';
    }
}
