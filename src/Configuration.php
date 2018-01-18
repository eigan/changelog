<?php

namespace Logg;

use Logg\Formatter\IFormatter;
use Logg\Formatter\MarkdownFormatter;
use Logg\Remotes\GitlabRemote;

class Configuration
{
    /**
     * @var string
     */
    private $rootPath;
    
    /**
     * @var string[]
     */
    private $data;

    /**
     * @var GitRepository
     */
    private $repository;
    
    public function __construct($rootPath, GitRepository $repository)
    {
        $this->rootPath = $rootPath;
        $this->repository = $repository;
    }

    /**
     * @return IFormatter
     */
    public function getConfiguredFormatter(): IFormatter
    {
        $config = $this->data['formatter'] ?? 'markdown';
        
        switch ($config) {
            default:
                return new MarkdownFormatter();
                break;
        }
    }
    
    public function getEntriesPath(): string
    {
        return $this->absolutePath($this->data['entries'] ?? '.changelogs');
    }
    
    public function getChangelogFilePath(): string
    {
        return $this->absolutePath($this->data['changelog'] ?? 'CHANGELOG') . '.md';
    }
    
    public function getConfiguredRemote(): ?GitlabRemote
    {
        $host = $this->repository->getHost();
        $project = $this->repository->getProject();
        
        if (isset($this->data['gitlab'])) {
            $gitlabToken = $this->data['gitlab']['token'] ?? null;

            if ($host && $project && $gitlabToken) {
                return new GitlabRemote($gitlabToken, $this->repository->getHost(), $this->repository->getProject());
            }
        }
        
        return null;
    }

    /**
     * @param  string $path
     * @return string
     */
    private function absolutePath(string $path): string
    {
        return $this->rootPath . '/' . $path;
    }
}
