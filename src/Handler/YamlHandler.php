<?php

declare(strict_types=1);

namespace Logg\Handler;

use Logg\Entry\Entry;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

class YamlHandler implements IEntryFileHandler
{
    public function getExtension(): string
    {
        return 'yml';
    }

    /**
     * Transform one entry into the contents of the file.
     */
    public function transform(Entry $entry): string
    {
        $properties = $entry->toArray();

        return "---\n".Yaml::dump($properties);
    }

    /**
     * Creates an Entry by the content of an entry file.
     *
     * @throws RuntimeException
     */
    public function parse(string $name, string $content): Entry
    {
        $properties = Yaml::parse($content);

        if (false === \is_array($properties)) {
            throw new RuntimeException('Invalid entry data. Got: '.$content);
        }

        return new Entry($name, $properties);
    }
}
