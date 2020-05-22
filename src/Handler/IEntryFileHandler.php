<?php

declare(strict_types=1);

namespace Logg\Handler;

use Logg\Entry\Entry;
use RuntimeException;

interface IEntryFileHandler
{
    public function getExtension(): string;

    public function transform(Entry $entry): string;

    /**
     * @throws RuntimeException if anything wrong with the content
     */
    public function parse(string $name, string $content): Entry;
}
