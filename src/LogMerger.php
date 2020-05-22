<?php

declare(strict_types=1);

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
     * Formats the entries and append it to the changelog.
     */
    public function append(string $content): void
    {
        // The formatter should append content
        $entireContent = $content."\n";

        $this->filesystem->appendChangelog($entireContent);
    }
}
