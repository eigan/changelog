<?php

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
     * @var null|string
     */
    public $description;
    
    public function __construct(string $key, string $label, string $description = null)
    {
        $this->key = $key;
        $this->label = $label;
        $this->description = $description;
    }
}
