<?php

namespace Logg\Tests\Integration;

use Logg\Configuration;
use Logg\Formatter\KeepAChangelogFormatter;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testParseConfigFile()
    {
        $changelogContent = [];
        $changelogContent[] = '# My content';
        $changelogContent[] = 'Here is a changelog, it describes stuff for us';
        
        $changelogContent[] = '## formatter: haxor';

        $changelogContent[] = '<!--';
        $changelogContent[] = 'formatter: keep-a-changelog';
        $changelogContent[] = 'entries: .our-custom-changelogs-path';
        $changelogContent[] = '-->';
        
        $changelogContent[] = '## 1.0';
        $changelogContent[] = '- My change';
        $changelogContent[] = '- formatter: haxor';
        
        $dir = vfsStream::setup('test', null, [
            'CHANGELOG.md' => implode("\n", $changelogContent)
        ]);
        
        $config = new Configuration($dir->url());
        
        $this->assertInstanceOf(KeepAChangelogFormatter::class, $config->getConfiguredFormatter());
        $this->assertEquals('vfs://test/.our-custom-changelogs-path', $config->getEntriesPath());
    }
}
