<?php

declare(strict_types=1);

namespace Logg\Formatter;

use Logg\Entry\EntryType;

interface IFormatter
{
    /**
     * @return EntryType[]
     */
    public function getSuggestedTypes(): array;

    /**
     * @param \Logg\Entry\Entry[]     $entries
     * @param array{minor: bool|null} $options
     *
     * @return mixed
     */
    public function format(string $headline, array $entries, array $options);
}
