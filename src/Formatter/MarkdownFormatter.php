<?php

namespace Logg\Formatter;

use Logg\Entry\Entry;

class MarkdownFormatter implements IFormatter
{
    // TODO: Formatter options

    /**
     * @param string  $headline
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
        $type = $entry->getType();

        $line = '* ';
        if (empty($type) === false) {
            $line .= "[{$entry->getType()}] ";
        }

        $line .= $entry->getTitle();

        if (strlen($entry->getAuthor()) > 0) {
            $line .= ' (' . $entry->getAuthor() . ')';
        }

        return $line;
    }
}
