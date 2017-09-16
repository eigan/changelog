<?php

namespace Logg\Entry;

class Entry
{
    protected $type;

    protected $title;

    protected $author;

    /**
     * All properties from file
     *
     * @var array
     */
    protected $all;

    public function __construct(string $title, string $type = null, string $author = null, array $all = [])
    {
        $this->type = $type;
        $this->title = $title;
        $this->author = $author;
        $this->all = $all;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
