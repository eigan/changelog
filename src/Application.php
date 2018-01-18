<?php

namespace Logg;

use Logg\Commands\EntryCommand;
use Logg\Commands\ReleaseCommand;
use Logg\Entry\EntryCollector;
use Logg\Entry\IEntryReferenceProvider;
use Logg\Formatter\IFormatter;
use Logg\Formatter\MarkdownFormatter;
use Logg\Handler\IEntryFileHandler;
use Logg\Handler\YamlHandler;
use Logg\Remotes\GitlabRemote;
use Logg\Remotes\IRemote;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var ?IRemote
     */
    private $remote;

    /**
     * @var IFormatter
     */
    private $formatter;
    
    public function __construct(string $rootPath)
    {
        parent::__construct('Log generator', 'dev');
        
        $this->repository = new GitRepository($rootPath);
        
        $this->handler = new YamlHandler();
        $this->config = new Configuration($rootPath, $this->repository);
        
        $this->filesystem = new Filesystem($this->config, $this->handler);
    }
    
    private function getRepository()
    {
        return $this->repository;
    }
    
    private function getRemote()
    {
        if(!$this->remote) {
            return $this->config->getConfiguredRemote();
        }
        
        return $this->remote;
    }
    
    public function getFormatter()
    {
        if(!$this->formatter) {
            return $this->formatter = $this->config->getConfiguredFormatter();
        }
        
        return $this->formatter;
    }
    

    protected function getDefaultCommands()
    {
        $parent =  parent::getDefaultCommands(); // TODO: Change the autogenerated stub

        $own = [
            
            new EntryCommand($this->handler, $this->filesystem, $this->getRepository(), $this->getRemote()),
            
            new ReleaseCommand(
                $this->filesystem,
                new EntryCollector($this->filesystem, $this->handler, $this->getRepository(), $this->getRemote()),
                new LogMerger($this->filesystem, $this->getFormatter()),
                $this->getFormatter()
            )
        ];

        return array_merge($parent, $own);
    }
}
