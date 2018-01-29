<?php

namespace Logg\Formatter;

use Logg\Entry\Entry;

class KeepAChangelogFormatter implements IFormatter
{
    private const HEADERS = [
        'new' => 'Added',
        'changed' => 'Changed',
        'deprecated' => 'Deprecated',
        'removed' => 'Removed',
        'fix' => 'Fixed',
        'security' => 'Security',
    ];
    
    /**
     * @inheritdoc
     */
    public function format(string $version, array $entries, array $options)
    {
        $lines = [];
    
        $lines[] = '## [' . $version . '] - ' . date('Y-m-d');
        
        $groupedEntries = $this->groupEntries($entries);
        
        foreach ($groupedEntries as $entryGroup) {
            $lines[] = '### ' . $entryGroup['header'];
            
            foreach ($entryGroup['entries'] as $entry) {
                $line = '- ' . $entry->getTitle();
        
                if (strlen($entry->getAuthor()) > 0) {
                    $line .= ' (' . $entry->getAuthor() . ')';
                }
                
                $lines[] = $line;
            }
            
            $lines[] = '';
        }
        
        return implode("\n", $lines);
    }

    /**
     * @param Entry[] $entries
     *
     * @return array
     */
    private function groupEntries(array $entries): array
    {
        $groups = [];
        
        foreach ($entries as $entry) {
            if (isset($groups[$entry->getType()]['entries']) === false) {
                $groups[$entry->getType()] = $this->setupGroup($entry->getType());
            }
            
            $groups[$entry->getType()]['entries'][] = $entry;
        }
        
        usort($groups, function ($firstGroup, $secondGroup) {
            $firstIndex = array_search($firstGroup['header'], array_values(self::HEADERS), true);
            $secondIndex = array_search($secondGroup['header'], array_values(self::HEADERS), true);
            
            return $firstIndex - $secondIndex;
        });
        
        return $groups;
    }
    
    private function setupGroup(string $type = null)
    {
        if ($type === null) {
            $type = 'unknown';
        }
        
        return [
            'header' => $this->translateTypeToHeading($type),
            'entries' => [],
        ];
    }
    
    private function translateTypeToHeading(string $type): string
    {
        return self::HEADERS[$type] ?? ucfirst($type);
    }
}
