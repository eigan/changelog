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
    
    public function getLastCommitAuthor(): ?string
    {
        return exec('cd '.$this->rootPath.' && git log -1 --pretty=format:\'%an\'');
    }

    /**
     * The repository host (gitlab.com)
     */
    public function getHost()
    {
        $remotes = exec('cd '.$this->rootPath.' && git remote -v');
        preg_match('/@(\w.+):/', $remotes, $matches);
        
        if (isset($matches[1])) {
            return $matches[1];
        }
        
        return null;
    }
    
    public function getProject()
    {
        $remotes = exec('cd '.$this->rootPath.' && git remote -v');
        preg_match('/\:(\w.+)\.git/', $remotes, $matches);
        
        if (isset($matches[1])) {
            return $matches[1];
        }
        
        return null;
    }
    
    public function getAllMerges(string $since): array
    {
        exec('cd '.$this->rootPath.' && git log --merges '.$since.'...HEAD --no-color', $lines);

        $i = -1;
        $commits = [];
        $commit = [];
        
        while (++$i > -1 && isset($lines[$i])) {
            $line = $lines[$i];
            
            if (strpos($line, 'commit ') === 0 && empty($commit) === false) {
                $commits[] = $commit;
                $commit = [];
            }
            
            
            $commit[] = $line;
        }
        
        return $commits;
    }
}
