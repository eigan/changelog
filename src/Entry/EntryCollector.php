<?php

namespace Logg\Entry;

use Logg\Filesystem;
use Logg\Handler\IEntryFileHandler;

class EntryCollector
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
     * Read all entry files, parse them and create Entry objects
     *
     * @return Entry[]
     */
    public function collect(): array
    {
        $entries = [];

        $files = $this->filesystem->getEntryContents();

        foreach ($files as $file) {
            $log = $this->parseLogEntry($file['content']);

            $entry = new Entry($file['filename'], $log);

            $entries[] = $entry;
        }

        // TODO: Should probably be somewhere else
        $typeOrder = [
            'new', 'fix', 'security', 'other'
        ];

        usort($entries, function ($firstEntry, $secondEntry) use ($typeOrder) {
            $firstIndex = array_search($firstEntry->getType(), $typeOrder) ?? 10;
            $secondIndex = array_search($secondEntry->getType(), $typeOrder) ?? 10;

            return $firstIndex - $secondIndex;
        });

        return $entries;
    }

    /**
     * Parse a single log entry (only yml)
     *
     * @param  string $content
     * @return array
     */
    private function parseLogEntry(string $content): array
    {
        return array_merge(
            [
                'title' => '',
                'type' => '',
                'author' => '',
            ],
            $this->handler->parse($content)
        );
    }
}
