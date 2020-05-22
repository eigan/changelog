<?php

declare(strict_types=1);

namespace Logg\Tests\Integration;

use Logg\Application;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use function str_replace;
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

    protected function setUp(): void
    {
        $this->testRoot = $this->createDirectory();

        $application = new Application($this->testRoot->url());
        $command = $application->find('entry');

        $this->commandTester = new CommandTester($command);
    }

    public function testAutoWithArgumentsAndOptions(): void
    {
        $this->execute([
            'title' => 'My entry title',
            '--type' => 'fix',
            '--author' => 'EG',
            '--name' => 'entry-file',
        ]);

        $entryPath = $this->testRoot->url().'/.changelogs/entry-file.yml';

        self::assertStringContentsEqualsFileContent($entryPath, "---
title: 'My entry title'
type: fix
author: EG
");
    }

    public function testAskForEverything(): void
    {
        $this->execute([], [], [
            'My entry title',
            '2',
            'EG',
            'Y',
        ]);

        $entryPath = $this->testRoot->url().'/.changelogs/my-entry-title.yml';

        self::assertStringContentsEqualsFileContent($entryPath, "---
title: 'My entry title'
type: fix
author: EG
");
    }

    public function testAskForEverything2(): void
    {
        $this->execute([], [], [
            'My entry title',
            'fix',
            'EG',
            'Y',
        ]);

        $entryPath = $this->testRoot->url().'/.changelogs/my-entry-title.yml';

        self::assertStringContentsEqualsFileContent($entryPath, "---
title: 'My entry title'
type: fix
author: EG
");
    }

    public function testNameCollision(): void
    {
        $dir = vfsStream::setup('test', null, [
            '.changelogs' => [
                'entry-file.yml' => '',
                'entry-file-1.yml' => '',
            ],
        ]);

        $application = new Application($dir->url());
        $command = $application->find('entry');

        $this->commandTester = new CommandTester($command);

        $this->execute([
            'title' => 'My entry title',
            '--type' => '2',
            '--author' => 'EG',
            '--name' => 'entry-file',
        ]);

        static::assertFileExists($dir->url().'/.changelogs/entry-file-2.yml');
    }

    public function testShouldCreateChangelogDir(): void
    {
        $dir = vfsStream::setup('test', null, []);

        $application = new Application($dir->url());
        $command = $application->find('entry');

        $this->commandTester = new CommandTester($command);

        $this->execute([
            'title' => 'My entry title',
            '--type' => '2',
            '--author' => 'EG',
            '--name' => 'entry-file',
        ]);

        $entryPath = $dir->url().'/.changelogs/entry-file.yml';

        self::assertStringContentsEqualsFileContent($entryPath, "---
title: 'My entry title'
type: fix
author: EG
");
    }

    public function testEmptyEntryTitle(): void
    {
        $output = $this->execute([
            'title' => '',
            '--type' => '2',
            '--author' => 'EG',
            '--name' => 'entry-file',
        ]);

        static::assertEquals("Missing entry title\n", $output);
    }

    public static function assertStringContentsEqualsFileContent(
        string $expectedFile,
        string $expected
    ): void {
        $actual = file_get_contents($expectedFile);
        $expected = str_replace("\r\n", "\n", $expected);

        static::assertEquals($expected, $actual);
    }

    /**
     * @return vfsStreamDirectory
     */
    private function createDirectory(array $structure = [])
    {
        if (empty($structure)) {
            $structure = [
                '.changelogs' => [
                ],
                'CHANGELOG.md' => '',
            ];
        }

        return vfsStream::setup('test', null, $structure);
    }

    private function execute($arguments, $options = [], $inputs = [])
    {
        $this->commandTester->setInputs($inputs);

        $this->commandTester->execute($arguments, $options + [
            'interactive' => !empty($inputs),
        ]);

        return str_replace("\r\n", "\n", $this->commandTester->getDisplay());
    }
}
