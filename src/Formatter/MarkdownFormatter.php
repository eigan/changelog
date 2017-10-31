<?php

namespace Logg\Formatter;

use Logg\Entry\Entry;
use Logg\Remotes\IRemote;

class MarkdownFormatter implements IFormatter
{
    /**
     * @var IRemote
     */
    private $remote;
    
    public function __construct(IRemote $remote = null)
    {
        $this->remote = $remote;
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

        if ($entry->getReference() && $this->remote) {
            $referenceUrl = $this->remote->getReferenceUrl($entry);
            $referenceText = $this->remote->getReferenceText($entry);

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
