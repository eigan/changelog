<?php

namespace Logg;

use Logg\Entry\Entry;
use Logg\Handler\IEntryFileHandler;

class Filesystem
{
    /**
     * @var string
     */
    private $changelogPath;

    /**
     * @var string
     */
    private $entriesPath;

    /**
     * @var IEntryFileHandler
     */
    private $handler;

    /**
     * Filesystem constructor.
     * @param Configuration     $configuration
     * @param IEntryFileHandler $handler
     */
    public function __construct(Configuration $configuration, IEntryFileHandler $handler)
    {
        $this->changelogPath = $configuration->getChangelogFilePath();
        $this->entriesPath = $configuration->getEntriesPath();
        $this->handler = $handler;
    }

    public function getChangelogPath(): string
    {
        return $this->changelogPath;
    }

    public function getEntriesPath(): string
    {
        return $this->entriesPath;
    }

    /**
     * Append content to changelog
     *
     * @param string $content
     */
    public function appendChangelog(string $content): void
    {
        $this->createChangelog();
        
        $content .= file_get_contents($this->changelogPath);

        file_put_contents($this->changelogPath, $content);
    }

    /**
     * @return array[] entries with plain content
     */
    public function getEntryContents(): array
    {
        $fileContents = [];
        
        if (file_exists($this->entriesPath) === false) {
            return [];
        }
        
        foreach (new \DirectoryIterator($this->entriesPath) as $file) {
            if ($file->isDot()) {
                continue;
            }

            $content = file_get_contents($this->entriesPath . '/' . $file->getFilename());

            if (empty($content)) {
                continue;
            }

            $fileContents[] = [
                'filename' => $file->getFilename(),
                'content' => $content
            ];
        }

        return $fileContents;
    }

    /**
     * Writes entry to chosen directory
     *
     * @param Entry $entry
     */
    public function writeEntry(Entry $entry): void
    {
        $this->createEntriesPath();
        
        $path = $this->entriesPath .'/'. $entry->getName() . '.' . $this->handler->getExtension();
        $content = $this->handler->transform($entry);

        if (file_exists($path)) {
            throw new \RuntimeException('Entry with same name exists. Please specify other name with \'-f\' option');
        }

        file_put_contents($path, $content);
    }

    /**
     * Remove everything in entries path
     *
     * TODO: Ensure we actually only delete entries..
     */
    public function cleanup(): void
    {
        foreach ($this->getEntryContents() as $entryContent) {
            unlink($this->entriesPath.'/'.$entryContent['filename']);
        }
    }
    
    private function createChangelog(): void
    {
        if (file_exists($this->changelogPath) === true) {
            return;
        }
        
        touch($this->changelogPath);
    }
    
    private function createEntriesPath(): void
    {
        if (file_exists($this->entriesPath) === true) {
            return;
        }
        
        mkdir($this->entriesPath, 0744, true);
    }
}
