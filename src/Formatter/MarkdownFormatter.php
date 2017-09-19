<?php

namespace Logg\Formatter;

use Logg\Entry\Entry;
use Logg\Entry\IEntryReferenceProvider;

class MarkdownFormatter implements IFormatter
{
    /**
     * @var IEntryReferenceProvider
     */
    private $referenceProvider;
    
    public function __construct(IEntryReferenceProvider $referenceProvider)
    {
        $this->referenceProvider = $referenceProvider;
    }

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

        if ($entry->getReference()) {
            $referenceUrl = $this->referenceProvider->getReferenceUrl($entry->getReference());
            $referenceText = $this->referenceProvider->getReferenceText($entry->getReference());

            if ($referenceUrl && $referenceText) {
                $line .= " [$referenceText]($referenceUrl)";
            } elseif (!$referenceUrl && $referenceText) {
                $line .= ' ' . $referenceText;
            }
        }
        
        if (strlen($entry->getAuthor()) > 0) {
            $line .= ' (' . $entry->getAuthor() . ')';
        }

        return $line;
    }
}
