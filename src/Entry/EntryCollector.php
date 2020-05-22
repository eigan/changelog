<?php

declare(strict_types=1);

namespace Logg\Entry;

use Logg\Filesystem;
use Logg\Handler\IEntryFileHandler;
use RuntimeException;

class EntryCollector
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IEntryFileHandler
     */
    private $handler;

    public function __construct(
        Filesystem $filesystem,
        IEntryFileHandler $handler
    ) {
        $this->filesystem = $filesystem;
        $this->handler = $handler;
    }

    /**
     * Read all entry files, parse them and create Entry objects.
     *
     * @return Entry[]
     */
    public function collect(): array
    {
        $entries = [];

        $files = $this->filesystem->getEntryContents();

        foreach ($files as $file) {
            try {
                $entry = $this->handler->parse($file['filename'], $file['content']);
            } catch (RuntimeException $e) {
                // TODO LOG
                continue;
            }

            $entries[] = $entry;
        }

        usort($entries, static function (Entry $firstEntry, Entry $secondEntry) {
            $firstIndex = array_search($firstEntry->getType(), Entry::TYPES, true);
            $firstIndex = false === $firstIndex ? 10 : $firstIndex;
            $secondIndex = array_search($secondEntry->getType(), Entry::TYPES, true);
            $secondIndex = false === $secondIndex ? 10 : $secondIndex;

            $typeCompare = $firstIndex - $secondIndex;

            if (0 === $typeCompare) {
                return strnatcmp($firstEntry->getTitle(), $secondEntry->getTitle());
            }

            return $firstIndex - $secondIndex;
        });

        return $entries;
    }
}
