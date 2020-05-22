<?php

declare(strict_types=1);

namespace Logg\Entry;

use InvalidArgumentException;

class Entry
{
    public const TYPES = ['fix', 'security', 'other', 'feature'];

    /**
     * @var string
     */
    protected $name;

    /**
     * All properties from file.
     *
     * @var array{title: string, type: string, author: string}
     */
    protected $all;

    /**
     * Entry constructor.
     *
     * @param array{title?: string, type?: string, author?: string} $all
     */
    public function __construct(string $name, array $all)
    {
        $this->name = $name;

        if (empty($all['title'])) {
            throw new InvalidArgumentException('Missing title in entry body');
        }

        $this->all['title'] = $all['title'] ?? '';
        $this->all['type'] = $all['type'] ?? '';
        $this->all['author'] = $all['author'] ?? '';
    }

    /**
     * A unique name for this entry.
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

    /**
     * @return array{title: string, type: string, author: string}
     */
    public function toArray(): array
    {
        return $this->all;
    }
}
