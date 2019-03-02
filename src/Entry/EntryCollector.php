<?php

namespace Logg\Entry;

use function is_array;
use Logg\Filesystem;
use Logg\Handler\IEntryFileHandler;

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
     * Read all entry files, parse them and create Entry objects
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
            } catch (\RuntimeException $e) {
                // TODO LOG
                continue;
            }

            $entries[] = $entry;
        }

        usort($entries, function (Entry $firstEntry, Entry $secondEntry) {
            $firstIndex = array_search($firstEntry->getType(), Entry::TYPES, true);
            $firstIndex = $firstIndex === false ? 10 : $firstIndex;
            $secondIndex = array_search($secondEntry->getType(), Entry::TYPES, true);
            $secondIndex = $secondIndex === false ? 10 : $secondIndex;
            
            if(is_array($firstIndex)) {
                $firstIndex = 10;
            }
            
            if(is_array($secondIndex)) {
                $secondIndex = 10;
            }
            
            $typeCompare = $firstIndex - $secondIndex;
            
            if ($typeCompare === 0) {
                return strnatcmp($firstEntry->getTitle(), $secondEntry->getTitle());
            }
            
            return $firstIndex - $secondIndex;
        });
        
        return $entries;
    }
}
