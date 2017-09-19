<?php

namespace Logg\Handler;

use Logg\Entry\Entry;

interface IEntryFileHandler
{
    public function transform(Entry $entry): string;

    /**
     * @param string $name
     * @param string $content
     *
     * @throws \RuntimeException if anything wrong with the content
     *
     * @return Entry
     */
    public function parse(string $name, string $content): Entry;
}
