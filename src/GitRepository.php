<?php

declare(strict_types=1);

namespace Logg;

class GitRepository
{
    /**
     * @var string
     */
    private $rootPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
    }

    public function getCurrentBranchName(): string
    {
        return exec('cd '.$this->rootPath.' && git rev-parse --abbrev-ref HEAD');
    }

    public function getLastCommitMessage(): ?string
    {
        exec('cd '.$this->rootPath.' && git log -1 --pretty=%B', $lines);

        if (\is_array($lines) && isset($lines[0])) {
            return $lines[0];
        }

        return null;
    }

    public function getLastCommitAuthor(): ?string
    {
        return exec('cd '.$this->rootPath.' && git log -1 --pretty=format:\'%an\'');
    }
}
