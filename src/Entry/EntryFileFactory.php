<?php

namespace Logg\Entry;

use Logg\Filesystem;
use Logg\Handler\IEntryFileHandler;

/**
 * Creates the entry file
 *
 * @package Logg\Entry
 */
class EntryFileFactory
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IEntryFileHandler
     */
    private $handler;

    public function __construct(
        Filesystem $filesystem,
        IEntryFileHandler $handler
    ) {
        $this->filesystem = $filesystem;
        $this->handler = $handler;
    }

    /**
     * @param Entry $entry
     *
     * @return EntryFile
     */
    public function generate(Entry $entry): EntryFile
    {
        $fileContent = $this->handler->parse($entry);
        $filename = $this->makeFilename($entry);

        return new EntryFile($filename, $fileContent);
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
        return str_replace([' '], ['-'], $entry->getTitle()) . '.yml';
    }
}
