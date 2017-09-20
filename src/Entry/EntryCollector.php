<?php

namespace Logg\Entry;

use Logg\Filesystem;
use Logg\GitRepository;
use Logg\Handler\IEntryFileHandler;
use Logg\Remotes\IRemote;

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

    /**
     * @var GitRepository
     */
    private $repository;

    /**
     * @var IRemote
     */
    private $remote;

    public function __construct(
        Filesystem $filesystem,
        IEntryFileHandler $handler,
        GitRepository $repository,
        IRemote $remote = null
    ) {
        $this->filesystem = $filesystem;
        $this->handler = $handler;
        $this->repository = $repository;
        $this->remote = $remote;
    }

    /**
     * Read all entry files, parse them and create Entry objects
     *
     * @return Entry[]
     */
    public function collect(string $since = null): array
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

        if ($since) {
            $entries = array_merge($entries, $this->findMergeEntries($since));
        }

        usort($entries, function ($firstEntry, $secondEntry) {
            $firstIndex = array_search($firstEntry->getType(), Entry::TYPES, true);
            $firstIndex = $firstIndex === false ? 10 : $firstIndex;
            $secondIndex = array_search($secondEntry->getType(), Entry::TYPES, true);
            $secondIndex = $secondIndex === false ? 10 : $secondIndex;
            
            return $firstIndex - $secondIndex;
        });
        
        return $entries;
    }

    private function findMergeEntries(string $since): array
    {
        $entries = [];

        // Go over each m
        $merges = $this->repository->getAllMerges($since);
        
        foreach ($merges as $merge) {
            $title = $this->extractTitle($merge);
            $type = $this->extractType($merge);
            $reference = $this->extractReference($merge);
            $author = $this->extractAuthor($merge);
            
            if (empty($title)) {
                continue;
            }
            
            $name = str_replace(' ', '-', $title);

            $entry = new Entry($name, [
                'title' => $title,
                'type' => $type,
                'reference' => $reference,
                'author' => $author
            ]);

            if ($this->remote && $reference) {
                $this->remote->decorate($entry);
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    private function extractTitle(array $message): ?string
    {
        if (isset($message[7])) {
            $title = trim($message[7]);
            
                       
            if (!empty($title) && strpos($title, 'Conflict: ') === false) {
                return $title;
            }
        }

        return null;
    }

    private function extractType(array $message): ?string
    {
        // TODO: Resolve gitlab merge request..
        return null;
    }

    private function extractReference(array $message): ?int
    {
        $combined = implode('', $message);

        preg_match_all('/\!(\d+)/', $combined, $matches, PREG_SET_ORDER, 0);

        if (isset($matches[0][1], $matches[0][1])) {
            return (int) $matches[0][1];
        }

        return null;
    }

    private function extractAuthor(array $message): ?string
    {
        $combined = implode("\n", $message);

        preg_match_all('/Author: (\w+)/', $combined, $matches, PREG_SET_ORDER, 0);

        if (isset($matches[0][1], $matches[0][1])) {
            return $matches[0][1];
        }

        return null;
    }
}
