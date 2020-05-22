<?php

declare(strict_types=1);

namespace Logg\Formatter;

use Logg\Entry\Entry;
use Logg\Entry\EntryType;

class KeepAChangelogFormatter implements IFormatter
{
    /**
     * {@inheritdoc}
     */
    public function getSuggestedTypes(): array
    {
        return [
            new EntryType('added', 'Added', 'for new features'),
            new EntryType('changed', 'Changed', 'for changes in existing functionality'),
            new EntryType('deprecated', 'Deprecated', 'for soon-to-be removed features'),
            new EntryType('removed', 'Removed', 'for now removed features'),
            new EntryType('fixed', 'Fixed', 'for any bug fixes'),
            new EntryType('security', 'Security', 'in case of vulnerabilities'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function format(string $version, array $entries, array $options)
    {
        $lines = [];

        $lines[] = '## ['.$version.'] - '.date('Y-m-d');

        $groupedEntries = $this->groupEntries($entries);

        foreach ($groupedEntries as $entryGroup) {
            $lines[] = '### '.$entryGroup['header'];

            foreach ($entryGroup['entries'] as $entry) {
                $line = '- '.$entry->getTitle();

                $author = $entry->getAuthor();

                if ($author && \strlen($author) > 0) {
                    $line .= ' ('.$entry->getAuthor().')';
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
     * @return array<string, array{entries: Entry[], header:string}>
     */
    private function groupEntries(array $entries): array
    {
        $groups = [];

        foreach ($entries as $entry) {
            if (false === isset($groups[$entry->getType()])) {
                $groups[$entry->getType()] = $this->setupGroup($entry->getType());
            }

            $groups[$entry->getType()]['entries'][] = $entry;
        }

        usort($groups, function ($firstGroup, $secondGroup) {
            $firstIndex = $this->getGroupPosition($firstGroup);
            $secondIndex = $this->getGroupPosition($secondGroup);

            return $firstIndex - $secondIndex;
        });

        return $groups;
    }

    /**
     * @return array{header: string, entries: Entry[]}
     */
    private function setupGroup(string $type = null)
    {
        if (null === $type) {
            $type = 'unknown';
        }

        return [
            'header' => $this->translateTypeToHeading($type),
            'entries' => [],
        ];
    }

    private function translateTypeToHeading(string $type): string
    {
        foreach ($this->getSuggestedTypes() as $suggestedType) {
            if ($type === $suggestedType->key) {
                return $suggestedType->label;
            }
        }

        return ucfirst($type);
    }

    /**
     * @param array{header: string, entries: Entry[]} $group
     */
    private function getGroupPosition($group): int
    {
        foreach ($this->getSuggestedTypes() as $index => $type) {
            if ($type->label === $group['header']) {
                return $index;
            }

            if ($type->key === $group['header']) {
                return $index;
            }
        }

        return \count($this->getSuggestedTypes());
    }
}
