<?php

namespace Logg\Entry\References;

use Logg\Entry\IEntryReferenceProvider;
use Symfony\Component\Console\Style\OutputStyle;

class GitlabReferenceProvider implements IEntryReferenceProvider
{
    public function askForReference(OutputStyle $output, string $default = null)
    {
        return $output->ask('Merge request ID', $default);
    }
    
    public function getReferenceText($reference)
    {
        return '!' . $reference;
    }
    
    public function getReferenceUrl($reference)
    {
        return '';
    }
}
