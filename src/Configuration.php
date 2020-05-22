<?php

declare(strict_types=1);

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

    public function __construct(string $rootPath)
    {
        $this->data = [];
        $this->rootPath = $rootPath;

        $this->parseConfig();
    }

    public function getConfiguredFormatter(): IFormatter
    {
        $config = $this->data['formatter'] ?? 'plain';

        switch ($config) {
            case 'keep-a-changelog':
                return new KeepAChangelogFormatter();

            default:
                return new PlainFormatter();
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

    private function absolutePath(string $path): string
    {
        return $this->rootPath.'/'.$path;
    }

    private function parseConfig(): void
    {
        $changelogPath = $this->getChangelogFilePath();

        if (file_exists($changelogPath)) {
            $handle = fopen($changelogPath, 'r');
            if ($handle) {
                while (false !== ($line = fgets($handle))) {
                    if (0 === strpos($line, 'formatter:')) {
                        $formatter = trim(substr($line, \strlen('formatter:')));
                    }

                    if (0 === strpos($line, 'entries:')) {
                        $entriesPath = trim(substr($line, \strlen('entries:')));
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
