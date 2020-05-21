<?php

namespace Logg\Tests\Integration;

use Logg\Application;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use function str_replace;

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
    
    public function setUp(): void
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
        
        self::assertStringContentsEqualsFileContent($entryPath, "---
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
            'Y'
        ]);

        $entryPath = $this->testRoot->url() . '/.changelogs/my-entry-title.yml';

        self::assertStringContentsEqualsFileContent($entryPath, "---
title: 'My entry title'
type: fix
author: EG
");
    }

    public function testAskForEverything2()
    {
        $this->execute([], [], [
            'My entry title',
            'fix',
            'EG',
            'Y'
        ]);

        $entryPath = $this->testRoot->url() . '/.changelogs/my-entry-title.yml';

        self::assertStringContentsEqualsFileContent($entryPath, "---
title: 'My entry title'
type: fix
author: EG
");
    }
    
    public function testNameCollision()
    {
        $dir = vfsStream::setup('test', null, [
            '.changelogs' => [
                'entry-file.yml' => '',
                'entry-file-1.yml' => ''
            ]
        ]);

        $application = new Application($dir->url());
        $command = $application->find('entry');

        $this->commandTester = new CommandTester($command);

        $this->execute([
            'title' => 'My entry title',
            '--type' => '2',
            '--author' => 'EG',
            '--name' => 'entry-file'
        ]);
        
        $this->assertFileExists($dir->url() . '/.changelogs/entry-file-2.yml');
    }
    
    public function testShouldCreateChangelogDir()
    {
        $dir = vfsStream::setup('test', null, []);

        $application = new Application($dir->url());
        $command = $application->find('entry');

        $this->commandTester = new CommandTester($command);

        $this->execute([
            'title' => 'My entry title',
            '--type' => '2',
            '--author' => 'EG',
            '--name' => 'entry-file'
        ]);

        $entryPath = $dir->url() . '/.changelogs/entry-file.yml';

        self::assertStringContentsEqualsFileContent($entryPath, "---
title: 'My entry title'
type: fix
author: EG
");
    }
    
    public function testEmptyEntryTitle()
    {
        $output = $this->execute([
            'title' => '',
            '--type' => '2',
            '--author' => 'EG',
            '--name' => 'entry-file'
        ]);
        
        $this->assertEquals("Missing entry title\n", $output);
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

        return str_replace("\r\n", "\n", $this->commandTester->getDisplay());
    }

    public static function assertStringContentsEqualsFileContent(
        string $expectedFile,
        string $expected
    ): void {
        $actual = file_get_contents($expectedFile);
        $expected = str_replace("\r\n", "\n", $expected);
        
        self::assertEquals($expected, $actual);
    }
}
