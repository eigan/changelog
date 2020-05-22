<?php

declare(strict_types=1);

namespace Logg\Entry;

class EntryType
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string|null
     */
    public $description;

    public function __construct(string $key, string $label, string $description = null)
    {
        $this->key = $key;
        $this->label = $label;
        $this->description = $description;
    }
}
