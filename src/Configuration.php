<?php

namespace Logg;

use Logg\Formatter\IFormatter;
use Logg\Formatter\KeepAChangelogFormatter;
use Logg\Formatter\PlainFormatter;

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
    
    public function __construct($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    /**
     * @return IFormatter
     */
    public function getConfiguredFormatter(): IFormatter
    {
        $config = $this->data['formatter'] ?? 'plain';
        
        switch ($config) {
            case 'keep-a-changelog':
                return new KeepAChangelogFormatter();
                
            default:
                return new PlainFormatter();
                break;
        }
    }
    
    public function getEntriesPath(): string
    {
        return $this->absolutePath($this->data['entries'] ?? '.changelogs');
    }
    
    public function getChangelogFilePath(): string
    {
        return $this->absolutePath($this->data['changelog'] ?? 'CHANGELOG.md');
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
