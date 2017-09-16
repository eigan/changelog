<?php

namespace Logg\Formatter;

use Logg\Entry\Entry;

class MarkdownFormatter
{
    // TODO: Formatter options

    /**
     * @param Entry[] $entries
     *
     * @return string
     */
    public function format(string $headline, array $entries): string
    {
        $content = '### ' . $headline . "\n";

        foreach ($entries as $entry) {
            $content .= $this->formatEntry($entry) . "\n";
        }

        return $content;
    }

    private function formatEntry(Entry $entry): string
    {
        return "* [{$entry->getType()}] " . $entry->getTitle();
    }
}
