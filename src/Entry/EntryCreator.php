<?php

namespace Logg\Entry;

use Logg\Filesystem;
use Symfony\Component\Yaml\Yaml;

class EntryCreator
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate(Entry $entry)
    {
        $logContent = $this->generateContent($entry);
        $filename = $this->makeFilename($entry);

        $this->filesystem->writeEntry($filename, $logContent);
    }

    private function generateContent(Entry $entry): string
    {
        $properties = $entry->toArray();

        $properties = array_filter($properties);

        return Yaml::dump($properties);
    }

    private function makeFilename(Entry $entry): string
    {
        return str_replace([' '], ['-'], $entry->getTitle()) . '.yml';
    }
}
