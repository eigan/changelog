<?php

namespace Logg\Entry;

class Entry
{
    const TYPES = ['fix', 'security', 'other', 'feature'];

    /**
     * @var string
     */
    protected $title;

    /**
     * All properties from file
     *
     * @var array
     */
    protected $all;

    public function __construct(string $title, array $all)
    {
        $this->title = $title;

        if (isset($all['title']) === false) {
            throw new \LogicException('Missing title in entry body');
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): ?string
    {
        return $this->all['type'] ?? null;
    }

    public function toArray(): array
    {
        return $this->all;
    }
}
