<?php

namespace Logg;

use InvalidArgumentException;
use Logg\Entry\EntryFile;

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
     * Filesystem constructor.
     *
     * @param string $changelogPath
     * @param string $entriesPath
     *
     */
    public function __construct(string $changelogPath, string $entriesPath)
    {
        if (file_exists($changelogPath) === false) {
            throw new InvalidArgumentException('Invalid changelog path');
        }

        if (is_writable($entriesPath) === false) {
            throw new InvalidArgumentException(('Entries path should be writeable'));
        }

        $this->changelogPath = $changelogPath;
        $this->entriesPath = $entriesPath;
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
        $content .= file_get_contents($this->changelogPath);

        file_put_contents($this->changelogPath, $content);
    }

    /**
     * @return string[] entries with plain content
     */
    public function getEntryContents(): array
    {
        $fileContents = [];
        
        foreach (new \DirectoryIterator($this->entriesPath) as $file) {
            if ($file->isDot()) {
                continue;
            }

            $fileContents[] = [
                'filename' => $file->getFilename(),
                'content' => file_get_contents($this->entriesPath . '/' . $file->getFilename())
            ];
        }

        return $fileContents;
    }

    /**
     * Writes entryfile to chosen directory
     *
     * @param EntryFile $entryFile
     */
    public function writeEntry(EntryFile $entryFile): void
    {
        $path = $this->entriesPath .'/'. $entryFile->getFilename();

        if (file_exists($path)) {
            throw new \RuntimeException('Entry with same name exists. Please specify other name with \'-f\' option');
        }

        file_put_contents($path, $entryFile->getContent());
    }

    /**
     * Remove everything in entries path
     *
     * TODO: Ensure we actually only delete entries..
     */
    public function cleanup(): void
    {
        foreach (new \DirectoryIterator($this->entriesPath) as $file) {
            if ($file->isDot()) {
                continue;
            }

            unlink($this->entriesPath.'/'.$file);
        }
    }
}
