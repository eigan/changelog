<?php

namespace Logg;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Logg\Formatter\MarkdownFormatter;

class LogMerger
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * LogMerger constructor.
     *
     * TODO: Require Formatter and Filesystem
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem(new Local(__DIR__ . '/../'));
    }

    /**
     * @param string $headline
     * @param array  $entries
     */
    public function append(string $headline, array $entries): void
    {
        // TODO: Not hardcode a formatter
        $formatter = new MarkdownFormatter();

        $content = $formatter->format($headline, $entries);

        // The formatter should append content
        $entireContent = $content . "\n" . $this->filesystem->read('CHANGELOG.md');

        $this->filesystem->put('CHANGELOG.md', $entireContent);
    }
}
