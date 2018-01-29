<?php

namespace Logg\Entry;

class EntryType
{
    public function __construct(string $key, string $label, string $description = null)
    {
        $this->key = $key;
        $this->label = $label;
        $this->description = $description;
    }
}
