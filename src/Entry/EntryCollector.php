<?php

namespace Logg\Entry;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class EntryCollector
{
    const CHANGELOG_DIR = 'changelogs';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem(new Local(__DIR__ . '/../../'));
    }

    /**
     * @return Entry[]
     */
    public function collect()
    {
        $entries = [];

        $files = $this->filesystem->listContents(self::CHANGELOG_DIR);

        foreach ($files as $file) {
            $log = $this->parseLogEntry($this->filesystem->read($file['path']));

            $entry = new Entry($log['title'], $log['type'], @$log['author'], $log);

            $entries[] = $entry;
        }

        return $entries;
    }

    private function parseLogEntry(string $content): array
    {
        // TODO: Support more than yaml

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
