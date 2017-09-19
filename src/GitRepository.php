<?php

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

    public function getCurrentBranchName()
    {
        return exec('cd '.$this->rootPath.' && git rev-parse --abbrev-ref HEAD');
    }

    public function getLastCommitMessage(): ?string
    {
        exec('cd '.$this->rootPath.' && git log -1 --pretty=%B', $lines);

        if (is_array($lines) && isset($lines[0])) {
            return $lines[0];
        }

        return null;
    }
}