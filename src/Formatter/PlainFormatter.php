<?php

namespace Logg\Formatter;

use Logg\Entry\Entry;
use Logg\Entry\EntryType;

class PlainFormatter implements IFormatter
{
    public function getSuggestedTypes(): array
    {
        return [
            new EntryType('new', 'New', 'for new features'),
            new EntryType('fix', 'Bugfix', 'for bugfixes'),
            new EntryType('security', 'Security', 'for security issues'),
            new EntryType('none', 'None'),
        ];
    }

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
        
        $author = $entry->getAuthor();
        
        if ($author && strlen($author) > 0) {
            $line .= ' (' . $entry->getAuthor() . ')';
        }

        return $line;
    }
}
