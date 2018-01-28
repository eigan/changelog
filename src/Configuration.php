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
        $this->data = [];
        $this->rootPath = $rootPath;
        
        $this->parseConfig();
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
        return $this->absolutePath('CHANGELOG.md');
    }

    /**
     * @param  string $path
     * @return string
     */
    private function absolutePath(string $path): string
    {
        return $this->rootPath . '/' . $path;
    }
    
    private function parseConfig(): void
    {
        $changelogPath = $this->getChangelogFilePath();
        
        if (file_exists($changelogPath)) {
            $handle = fopen($changelogPath, 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (strpos($line, 'formatter:') === 0) {
                        $formatter = trim(substr($line, strlen('formatter:')));
                    }
                    
                    if (strpos($line, 'entries:') === 0) {
                        $entriesPath = trim(substr($line, strlen('entries:')));
                    }
                }

                fclose($handle);
            }
        }
        
        if (isset($formatter)) {
            $this->data['formatter'] = $formatter;
        }
        
        if (isset($entriesPath)) {
            $this->data['entries'] = $entriesPath;
        }
    }
}
