<?php

namespace Logg\Tests\Integration;

use Logg\Application;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class EntryCommandTest extends TestCase
{

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var vfsStreamDirectory
     */
    private $testRoot;
    
    public function setUp()
    {
        $this->testRoot = $this->createDirectory();

        $application = new Application($this->testRoot->url());
        $command = $application->find('entry');

        $this->commandTester = new CommandTester($command);
    }
    
    public function testAutoWithArgumentsAndOptions()
    {
        $this->execute([
            'title' => 'My entry title',
            '--type' => 'fix',
            '--author' => 'EG',
            '--name' => 'entry-file'
        ]);
        
        $entryPath = $this->testRoot->url() . '/.changelogs/entry-file.yml';
        
        $this->assertStringEqualsFile($entryPath, "---
title: 'My entry title'
type: fix
author: EG
");
    }

    public function testAskForEverything()
    {
        $this->execute([], [], [
            'My entry title',
            '2',
            'EG',
            'entry-file',
            'Y'
        ]);

        $entryPath = $this->testRoot->url() . '/.changelogs/entry-file.yml';

        $this->assertStringEqualsFile($entryPath, "---
title: 'My entry title'
type: fix
author: EG
");
    }
    
    /**
     * @param  array              $structure
     * @return vfsStreamDirectory
     */
    private function createDirectory(array $structure = [])
    {
        if (empty($structure)) {
            $structure = [
                '.changelogs' => [

                ],
                'CHANGELOG.md' => ''
            ];
        }
        
        return vfsStream::setup('test', null, $structure);
    }

    private function execute($arguments, $options = [], $inputs = [])
    {
        $this->commandTester->setInputs($inputs);

        $this->commandTester->execute($arguments, $options + [
                'interactive' => !empty($inputs)
            ]);

        return $this->commandTester->getDisplay();
    }
}
