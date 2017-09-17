<?php

namespace Logg\Entry;

use Logg\Filesystem;
use Symfony\Component\Yaml\Yaml;

class EntryCollector
{

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Read all entry files, parse them and create Entry objects
     *
     * @return Entry[]
     */
    public function collect()
    {
        $entries = [];

        $files = $this->filesystem->getEntryContents();

        foreach ($files as $file) {
            $log = $this->parseLogEntry($file['content']);

            $entry = new Entry($log['title'], $log);

            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * Parse a single log entry (only yml)
     * TODO: Support more than yml
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
            Yaml::parse($content)
        );
    }
}
