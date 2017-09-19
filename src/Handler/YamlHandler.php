<?php

namespace Logg\Handler;

use Logg\Entry\Entry;
use Symfony\Component\Yaml\Yaml;

class YamlHandler implements IEntryFileHandler
{
    public function transform(Entry $entry): string
    {
        $properties = $entry->toArray();

        return "---\n" . Yaml::dump($properties);
    }

    /**
     * Creates an Entry by the content of an entry file
     *
     * @param string $name
     * @param string $content
     *
     * @throws \RuntimeException
     *
     * @return Entry
     */
    public function parse(string $name, string $content): Entry
    {
        $properties = Yaml::parse($content);

        if (is_array($properties) === false) {
            throw new \RuntimeException('Invalid entry data. Got: ' . $content);
        }

        return new Entry($name, $properties);
    }
}
