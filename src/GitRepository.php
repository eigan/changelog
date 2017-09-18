<?php

namespace Logg;

class GitRepository extends \Cz\Git\GitRepository
{
    public function getLastCommitMessage(): ?string
    {
        $lines = $this->extractFromCommand('git log -1 --pretty=%B', 'trim');

        if (is_array($lines) && isset($lines[0])) {
            return $lines[0];
        }

        return null;
    }
}
