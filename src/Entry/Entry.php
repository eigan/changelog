<?php

namespace Logg\Entry;

class Entry
{
    const TYPES = ['fix', 'security', 'other', 'feature'];

    /**
     * @var string
     */
    protected $name;

    /**
     * All properties from file
     *
     * @var array
     */
    protected $all;

    public function __construct(string $name, array $all)
    {
        $this->name = $name;

        if (empty($all['title'])) {
            throw new \InvalidArgumentException('Missing title in entry body');
        }

        $this->all = array_merge(
            [
                'title' => '',
                'type' => '',
                'author' => ''
            ],
            $all
        );
    }

    /**
     * A unique name for this entry
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->all['title'];
    }

    public function getType(): ?string
    {
        return $this->all['type'] ?? null;
    }

    public function getAuthor(): ?string
    {
        return $this->all['author'] ?? null;
    }

    public function toArray(): array
    {
        return $this->all;
    }
}
