<?php

declare(strict_types=1);

namespace Logg\Tests\Integration;

use function file_get_contents;
use Logg\Application;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use function str_replace;
use Symfony\Component\Console\Tester\CommandTester;

class ReleaseCommandTest extends TestCase
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
        $command = $application->find('release');

        $this->commandTester = new CommandTester($command);
    }

    public function testStandardCommand(): void
    {
        $this->execute([
            'headline' => '1.0',
        ]);

        $changelogPath = $this->testRoot->url().'/CHANGELOG.md';

        $this->assertStringContentsEqualsFileContent($changelogPath, '#### 1.0
* [fix] Foo bar (EG)
* [fix] Foobar! (EG)
* [fix] My entry title (EG)
* [new] Abc (EG)

');
    }

    public function testPreview(): void
    {
        $output = $this->execute([
            'headline' => '1.0',
            '--preview' => true,
        ]);

        static::assertEquals(str_replace("\r\n", "\n", '#### 1.0
* [fix] Foo bar (EG)
* [fix] Foobar! (EG)
* [fix] My entry title (EG)
* [new] Abc (EG)
'), $output);
    }

    public function testJsonPreview(): void
    {
        $output = $this->execute([
            'headline' => '1.0',
            '--preview-json' => true,
        ]);

        static::assertEquals($output, '{"headline":"1.0","entries":[{"title":"Foo bar","type":"fix","author":"EG"},{"title":"Foobar!","type":"fix","author":"EG"},{"title":"My entry title","type":"fix","author":"EG"},{"title":"Abc","type":"new","author":"EG"}]}');
    }

    public function testInvalidYamlFile(): void
    {
        $structure = [
            '.changelogs' => [
                'my-entry.yml' => "invalid
                foo\:bar pp
    ",
            ],
        ];

        $dir = vfsStream::setup('test', null, $structure);

        $application = new Application($this->testRoot->url());
        $command = $application->find('release');

        $this->commandTester = new CommandTester($command);

        $output = $this->execute([
            'headline' => '1.0',
        ]);

        static::assertEquals("No entries to append\n", $output);
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
                    'my-entry.yml' => "---
title: 'My entry title'
type: fix
author: EG
",

                    'my-entry-2.yml' => "---
title: 'Foobar!'
type: fix
author: EG
",

                    'my-entry-3.yml' => "---
title: 'Abc'
type: new
author: EG
",
                    'my-entry-4.yml' => "---
title: 'Foo bar'
type: fix
author: EG
",
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
