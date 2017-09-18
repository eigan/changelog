<?php

namespace Logg\Handler;

use Logg\Entry\Entry;

interface IEntryFileHandler
{
    public function transform(Entry $entry): string;

    public function parse(string $content): array;
}
