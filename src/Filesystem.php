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
                'file' => $file,
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
        file_put_contents($this->entriesPath .'/'. $entryFile->getFilename(), $entryFile->getContent());
    }

    /**
     * Remove everything in entries path
     *
     * TODO: Ensure we actually delete entries..
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
