<?php

namespace Logg\Tests\Integration;

use Logg\Application;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
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

    public function setUp()
    {
        $this->testRoot = $this->createDirectory();

        $application = new Application($this->testRoot->url());
        $command = $application->find('release');

        $this->commandTester = new CommandTester($command);
    }

    public function testStandardCommand()
    {
        $this->execute([
            'headline' => '1.0',
        ]);
        
        $changelogPath = $this->testRoot->url() . '/CHANGELOG.md';
        
        $this->assertStringEqualsFile($changelogPath, '#### 1.0
* [fix] My entry title (EG)

');
    }

    public function testPreview()
    {
        $output = $this->execute([
            'headline' => '1.0',
            '--preview' => true
        ]);
        
        $this->assertEquals($output, '#### 1.0
* [fix] My entry title (EG)
');
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
                    'my-entry.yml' => "---
title: 'My entry title'
type: fix
author: EG
reference: ''
"
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
