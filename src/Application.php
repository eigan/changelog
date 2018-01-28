<?php

namespace Logg;

use Logg\Commands\EntryCommand;
use Logg\Commands\ReleaseCommand;
use Logg\Entry\EntryCollector;
use Logg\Formatter\IFormatter;
use Logg\Handler\IEntryFileHandler;
use Logg\Handler\YamlHandler;

class Application extends \Symfony\Component\Console\Application
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var GitRepository
     */
    private $repository;

    /**
     * @var IEntryFileHandler
     */
    private $handler;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * @var IFormatter
     */
    private $formatter;
    
    public function __construct(string $rootPath)
    {
        $this->setupRepository($rootPath);

        $this->handler = new YamlHandler();
        $this->config = new Configuration($rootPath);

        $this->filesystem = new Filesystem($this->config, $this->handler);
        
        parent::__construct('Log generator', 'dev');
    }
    
    public function getFormatter()
    {
        if (!$this->formatter) {
            return $this->formatter = $this->config->getConfiguredFormatter();
        }
        
        return $this->formatter;
    }

    protected function getDefaultCommands()
    {
        $parent =  parent::getDefaultCommands(); // TODO: Change the autogenerated stub

        $own = [
            
            new EntryCommand($this->handler, $this->filesystem, $this->repository),
            
            new ReleaseCommand(
                $this->filesystem,
                new EntryCollector($this->filesystem, $this->handler),
                new LogMerger($this->filesystem, $this->getFormatter()),
                $this->getFormatter()
            )
        ];

        return array_merge($parent, $own);
    }
    
    private function setupRepository($rootPath)
    {
        if (file_exists($rootPath . '/.git')) {
            $this->repository = new GitRepository($rootPath);
        }
    }
}
