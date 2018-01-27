<?php

namespace Logg\Formatter;

use Logg\Entry\Entry;

class MarkdownFormatter implements IFormatter
{
    /**
     * @inheritdoc
     */
    public function format(string $headline, array $entries, array $options): string
    {
        $content = '#### ' . $headline . "\n";
        
        if ($options['minor']) {
            // Reset header to subheader
            $content = '### ' . $headline . "\n";
        }

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
