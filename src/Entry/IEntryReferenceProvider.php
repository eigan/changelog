<?php

namespace Logg\Entry;

use Symfony\Component\Console\Style\OutputStyle;

interface IEntryReferenceProvider
{
    public function askForReference(OutputStyle $output, string $default);
    
    public function getReferenceUrl($reference);
    
    public function getReferenceText($reference);
}
