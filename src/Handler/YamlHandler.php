<?php

namespace Logg\Handler;

use Logg\Entry\Entry;
use Symfony\Component\Yaml\Yaml;

class YamlHandler implements IEntryFileHandler
{
    public function transform(Entry $entry): string
    {
        $properties = $entry->toArray();

        return Yaml::dump($properties);
    }

    public function parse(string $content): array
    {
        return Yaml::parse($content);
    }
}
