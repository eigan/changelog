<?php

namespace Logg\Handler;

use Logg\Entry\Entry;

interface IEntryFileHandler
{
    public function parse(Entry $entry): string;
}
