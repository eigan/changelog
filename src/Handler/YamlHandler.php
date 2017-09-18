<?php

namespace Logg\Handler;

use Logg\Entry\Entry;
use Symfony\Component\Yaml\Yaml;

class YamlHandler implements IEntryFileHandler
{
    public function parse(Entry $entry): string
    {
        $properties = $entry->toArray();

        return Yaml::dump($properties);
    }
}
