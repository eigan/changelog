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
                'type' => 'fixed',
                'author' => 'EG'
            ]),
            
            new Entry('my-entry2', [
                'title' => 'Keep a changelog please',
                'type' => 'added',
                'author' => 'EG'
            ]),
            
            new Entry('my-entry2', [
                'title' => 'Keep a changelog please',
                'type' => 'removed',
                'author' => 'EG'
            ]),
            
            new Entry('my-2ntryu', [
                'title' => 'Keep a changelog please',
                'type' => 'security',
                'author' => 'EG'
            ]),
            
            new Entry('my-2ntryu', [
                'title' => 'Keep a changelog please',
                'type' => 'changed',
                'author' => 'EG'
            ]),
            
            new Entry('my-2ntryu', [
                'title' => 'Keep a changelog please',
                'type' => 'deprecated',
                'author' => 'EG'
            ])
        ], []);
        
        $lines = [];
        $lines[] = '## [1.0] - ' . date('Y-m-d');
        $lines[] = '### Added';
        $lines[] = '- Keep a changelog please (EG)';
        $lines[] = '';
        $lines[] = '### Changed';
        $lines[] = '- Keep a changelog please (EG)';
        $lines[] = '';
        $lines[] = '### Deprecated';
        $lines[] = '- Keep a changelog please (EG)';
        $lines[] = '';
        $lines[] = '### Removed';
        $lines[] = '- Keep a changelog please (EG)';
        $lines[] = '';
        $lines[] = '### Fixed';
        $lines[] = '- Keep a changelog please (EG)';
        $lines[] = '';
        $lines[] = '### Security';
        $lines[] = '- Keep a changelog please (EG)';
        $lines[] = '';
        
        $this->assertEquals(implode("\n", $lines), $result);
    }
}
