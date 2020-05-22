<?php

declare(strict_types=1);

namespace Logg\Tests\Integration;

use Logg\Configuration;
use Logg\Formatter\KeepAChangelogFormatter;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testParseConfigFile(): void
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
            'CHANGELOG.md' => implode("\n", $changelogContent),
        ]);

        $config = new Configuration($dir->url());

        static::assertInstanceOf(KeepAChangelogFormatter::class, $config->getConfiguredFormatter());
        static::assertEquals('vfs://test/.our-custom-changelogs-path', $config->getEntriesPath());
    }
}
