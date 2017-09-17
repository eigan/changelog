<?php

namespace Logg;

use Logg\Formatter\IFormatter;

class LogMerger
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IFormatter
     */
    private $formatter;

    /**
     * LogMerger constructor.
     *
     * TODO: Require Formatter and Filesystem
     */
    public function __construct(Filesystem $filesystem, IFormatter $formatter)
    {
        $this->filesystem = $filesystem;
        $this->formatter = $formatter;
    }

    /**
     * Formats the entries and append it to the changelog
     *
     * @param string $headline
     * @param array  $entries
     */
    public function append(string $headline, array $entries): void
    {
        $content = $this->formatter->format($headline, $entries);

        // The formatter should append content
        $entireContent = $content . "\n";

        $this->filesystem->appendChangelog($entireContent);
    }
}
