<?php

namespace Logg\Tests\Integration;

use Logg\Entry\Entry;
use Logg\Formatter\KeepAChangelogFormatter;
use PHPStan\Testing\TestCase;

class KeepAChangelogFormatterTest extends TestCase
{
    public function testOrder()
    {
        $formatter = new KeepAChangelogFormatter();
        
        $result = $formatter->format('1.0', [
            new Entry('my-entry', [
                'title' => 'Keep a changelog please',
                'type' => 'fixed'
            ]),
            
            new Entry('my-entry2', [
                'title' => 'Keep a changelog please',
                'type' => 'added'
            ]),
            
            new Entry('my-entry2', [
                'title' => 'Keep a changelog please',
                'type' => 'removed'
            ]),
            
            new Entry('my-2ntryu', [
                'title' => 'Keep a changelog please',
                'type' => 'security'
            ]),
            
            new Entry('my-2ntryu', [
                'title' => 'Keep a changelog please',
                'type' => 'changed'
            ]),
            
            new Entry('my-2ntryu', [
                'title' => 'Keep a changelog please',
                'type' => 'deprecated'
            ])
        ], []);
        
        $lines = [];
        $lines[] = '## [1.0] - ' . date('Y-m-d');
        $lines[] = '### Added';
        $lines[] = '- Keep a changelog please';
        $lines[] = '';
        $lines[] = '### Changed';
        $lines[] = '- Keep a changelog please';
        $lines[] = '';
        $lines[] = '### Deprecated';
        $lines[] = '- Keep a changelog please';
        $lines[] = '';
        $lines[] = '### Removed';
        $lines[] = '- Keep a changelog please';
        $lines[] = '';
        $lines[] = '### Fixed';
        $lines[] = '- Keep a changelog please';
        $lines[] = '';
        $lines[] = '### Security';
        $lines[] = '- Keep a changelog please';
        $lines[] = '';
        
        $this->assertEquals(implode("\n", $lines), $result);
    }
}
